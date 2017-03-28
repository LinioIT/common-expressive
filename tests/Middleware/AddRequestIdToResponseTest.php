<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class AddRequestIdToResponseTest extends TestCase
{
    public function testItAddsTheRequestIdToTheResponse()
    {
        $requestId = 'testId';

        $request = (new ServerRequest())->withAttribute('requestId', $requestId);
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $middleware = new AddRequestIdToResponse();
        $actual = $middleware->process($request, $delegate);

        $this->assertSame($requestId, $actual->getHeader('X-Request-Id')[0]);
    }

    public function testItFailsWhenThereIsNoRequestId()
    {
        $request = new ServerRequest();
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new AddRequestIdToLog();
        $middleware->process($request, $delegate);
    }
}
