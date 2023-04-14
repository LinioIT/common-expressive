<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Eloquent\Phony\Phpunit\Phony;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Exception\Http\ContentTypeNotSupportedException;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Middleware\ValidateSupportedContentTypes;
use Linio\TestAssets\TestMiddleware;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ValidateSupportedContentTypesTest extends TestCase
{
    /**
     * @dataProvider unsupportedContentTypeRequestProvider
     */
    public function testItOnlyAllowsSupportedContentTypes(ServerRequestInterface $request): void
    {
        $response = new Response();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(ContentTypeNotSupportedException::class);

        $middleware = new ValidateSupportedContentTypes([], []);
        $middleware->__invoke($request, $response, $callable);
    }

    public function testItUsesRouteSpecificOverrides(): void
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $route = new Route('test_valid_content_type', new TestMiddleware(), ['GET'], 'test_valid_content_type');
        $request = (new ServerRequest([], [], '/valid-content-type'))
            ->withHeader('Content-Type', 'supported')
            ->withAttribute(RouteResult::class, RouteResult::fromRoute($route));
        $response = new Response();
        $expected = new EmptyResponse();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) use ($expected) {
            return $expected;
        };

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $actual = $middleware->__invoke($request, $response, $callable);

        $this->assertSame($expected, $actual);
    }

    public function testItAllowsNoContentTypesForStandardPages(): void
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $route = new Route('/no-content-type', new TestMiddleware(), ['GET'], 'test_no_content_type');
        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, RouteResult::fromRoute($route));
        $response = new Response();
        $callable = Phony::spy(function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        });

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $middleware->supportType(null);
        $middleware->__invoke($request, $response, $callable);

        $callable->called();
    }

    public function testItRequiresTheRouterMiddlewareToHaveBeenRun(): void
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
        $route = new Route('test', new TestMiddleware(), ['GET'], 'test_valid_content_type');
        $routeResult = RouteResult::fromRoute($route);

        return [
            [(new ServerRequest())->withAttribute(RouteResult::class, $routeResult)],
            [(new ServerRequest())->withHeader('Content-Type', 'unsupported')->withAttribute(RouteResult::class, $routeResult)],
        ];
    }
}
