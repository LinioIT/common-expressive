<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Tests\Middleware;

use Linio\Common\Mezzio\Logging\LogRequestResponseService;
use Linio\Common\Mezzio\Middleware\LogRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;

class LogRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testItCallsLogRequestResponseService()
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
