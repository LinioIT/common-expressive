<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Middleware;

use Linio\Common\Expressive\Logging\LogRequestResponseService;
use Linio\Common\Expressive\Middleware\LogRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

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
