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

        $middleware = new ValidateSupportedContentTypes();
        $middleware->__invoke($request, $response, $callable);
    }

    public function testItAllowsApplicationJsonByDefault()
    {
        $request = (new ServerRequest())->withHeader('Content-Type', 'application/json');
        $response = new Response();
        $callable = Phony::spy(function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        });

        $middleware = new ValidateSupportedContentTypes();
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
