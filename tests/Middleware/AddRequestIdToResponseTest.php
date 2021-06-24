<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Middleware;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Expressive\Middleware\AddRequestIdToLog;
use Linio\Common\Expressive\Middleware\AddRequestIdToResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
        $callable = function ($request, $response) {
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
