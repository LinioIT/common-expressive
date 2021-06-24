<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Middleware;

use Linio\Common\Expressive\Logging\LogRequestResponseService;
use Linio\Common\Expressive\Middleware\LogResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class LogResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testItCallsLogRequestResponseService()
    {
        $request = new ServerRequest();
        $response = new Response();

        $logRequestResponseService = $this->prophesize(LogRequestResponseService::class);
        $logRequestResponseService
            ->logResponse($request, $response)
            ->shouldBeCalled();

        $next = function ($request, $response) {
            return $response;
        };

        $middleware = new LogResponse($logRequestResponseService->reveal());
        $middleware->__invoke($request, $response, $next);
    }
}
