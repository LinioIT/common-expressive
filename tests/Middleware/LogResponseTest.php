<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Logging\LogRequestResponseService;
use Linio\Common\Laminas\Middleware\LogResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\RequestHandlerInterface;

class LogResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testItCallsLogRequestResponseService(): void
    {
        $request = new ServerRequest();
        $response = new Response();

        $logRequestResponseService = $this->prophesize(LogRequestResponseService::class);
        $logRequestResponseService
            ->logResponse($request, $response)
            ->shouldBeCalled();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $middleware = new LogResponse($logRequestResponseService->reveal());
        $middleware->process($request, $handler->reveal());
    }
}
