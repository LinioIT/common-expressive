<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Eloquent\Phony\Phpunit\Phony;
use Linio\Common\Expressive\Exception\Http\ContentTypeNotSupportedException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class ValidateSupportedContentTypesTest extends TestCase
{
    /**
     * @dataProvider unsupportedContentTypeRequestProvider
     *
     * @param ServerRequestInterface $request
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

        $request = (new ServerRequest())->withHeader('Content-Type', 'supported')->withRequestTarget('/valid-content-type');
        $response = new Response();
        $callable = Phony::spy(function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        });

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $middleware->__invoke($request, $response, $callable);

        $callable->called();
    }

    public function testItAllowsNoContentTypesForStandardPages()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $request = new ServerRequest();
        $response = new Response();
        $callable = Phony::spy(function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        });

        $middleware = new ValidateSupportedContentTypes([], $routes);
        $middleware->supportType(null);
        $middleware->__invoke($request, $response, $callable);

        $callable->called();
    }

    public function unsupportedContentTypeRequestProvider(): array
    {
        return [
            [new ServerRequest()],
            [(new ServerRequest())->withHeader('Content-Type', 'unsupported')],
        ];
    }
}
