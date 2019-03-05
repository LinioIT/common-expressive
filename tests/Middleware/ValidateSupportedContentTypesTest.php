<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Eloquent\Phony\Phony;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Linio\Common\Expressive\Exception\Http\ContentTypeNotSupportedException;
use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;

class ValidateSupportedContentTypesTest extends TestCase
{
    /**
     * @dataProvider unsupportedContentTypeRequestProvider
     */
    public function testItOnlyAllowsSupportedContentTypes(ServerRequestInterface $request)
    {
        $response = new Response();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(ContentTypeNotSupportedException::class);

        $middleware = new ValidateSupportedContentTypes([], []);
        $middleware->__invoke($request, $response, $callable);
    }

    public function testItUsesRouteSpecificOverrides()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $middleware = $this->prophesize(MiddlewareInterface::class);
        $request = (new ServerRequest([], [], '/valid-content-type'))
            ->withHeader('Content-Type', 'supported')
            ->withAttribute(RouteResult::class, RouteResult::fromRoute(new Route('test_valid_content_type', $middleware->reveal()), []));
        $response = new Response();
        $expected = new EmptyResponse();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) use ($expected) {
            return $expected;
        };

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $actual = $middleware->__invoke($request, $response, $callable);

        $this->assertSame($expected, $actual);
    }

    public function testItAllowsNoContentTypesForStandardPages()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $middleware = $this->prophesize(MiddlewareInterface::class);
        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, RouteResult::fromRoute(new Route('test_no_content_type', $middleware->reveal()), []));
        $response = new Response();
        $callable = Phony::spy(function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        });

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $middleware->supportType(null);
        $middleware->__invoke($request, $response, $callable);

        $callable->called();
    }

    public function testItRequiresTheRouterMiddlewareToHaveBeenRun()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $request = new ServerRequest();
        $response = new Response();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $middleware->__invoke($request, $response, $callable);
    }

    public function unsupportedContentTypeRequestProvider(): array
    {
        $middleware = $this->prophesize(MiddlewareInterface::class);
        $routeResult = RouteResult::fromRoute(new Route('test', $middleware->reveal()), []);

        return [
            [(new ServerRequest())->withAttribute(RouteResult::class, $routeResult)],
            [(new ServerRequest())->withHeader('Content-Type', 'unsupported')->withAttribute(RouteResult::class, $routeResult)],
        ];
    }
}
