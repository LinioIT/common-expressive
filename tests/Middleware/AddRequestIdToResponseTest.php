<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class AddRequestIdToResponseTest extends TestCase
{
    public function testItAddsTheRequestIdToTheResponse()
    {
        $requestId = 'testId';

        $request = (new ServerRequest())->withAttribute('requestId', $requestId);
        $response = new Response();
        $callable = function ($request, $response) use ($requestId) {
            return new EmptyResponse();
        };

        $middleware = new AddRequestIdToResponse();
        $actual = $middleware->__invoke($request, $response, $callable);

        $this->assertSame($requestId, $actual->getHeader('X-Request-Id')[0]);
    }

    public function testItFailsWhenThereIsNoRequestId()
    {
        $request = new ServerRequest();
        $response = new Response();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new AddRequestIdToLog();
        $middleware->__invoke($request, $response, $callable);
    }
}
