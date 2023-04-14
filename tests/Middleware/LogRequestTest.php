<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Logging\LogRequestResponseService;
use Linio\Common\Laminas\Middleware\LogRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

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
        $next = function ($request, $response) {
            return new EmptyResponse();
        };

        $middleware = new LogRequest($logRequestResponseService->reveal());
        $middleware->__invoke($request, $response, $next);
    }
}
