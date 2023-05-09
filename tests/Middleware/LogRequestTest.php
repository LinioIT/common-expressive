<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Logging\LogRequestResponseService;
use Linio\Common\Laminas\Middleware\LogRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\RequestHandlerInterface;

class LogRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testItCallsLogRequestResponseService(): void
    {
        $request = new ServerRequest();

        $logRequestResponseService = $this->prophesize(LogRequestResponseService::class);
        $logRequestResponseService
            ->logRequest($request)
            ->shouldBeCalled();

        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $middleware = new LogRequest($logRequestResponseService->reveal());
        $middleware->process($request, $handler->reveal());
    }
}
