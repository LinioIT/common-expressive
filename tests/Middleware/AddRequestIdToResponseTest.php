<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Middleware\AddRequestIdToLog;
use Linio\Common\Laminas\Middleware\AddRequestIdToResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\RequestHandlerInterface;

class AddRequestIdToResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testItAddsTheRequestIdToTheResponse(): void
    {
        $requestId = 'testId';

        $request = (new ServerRequest())->withAttribute('requestId', $requestId);
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $middleware = new AddRequestIdToResponse();
        $actual = $middleware->process($request, $handler->reveal());

        $this->assertSame($requestId, $actual->getHeader('X-Request-Id')[0]);
    }

    public function testItFailsWhenThereIsNoRequestId(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new AddRequestIdToLog();
        $middleware->process($request, $handler->reveal());
    }
}
