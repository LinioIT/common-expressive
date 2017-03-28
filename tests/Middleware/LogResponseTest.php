<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Linio\Common\Expressive\Logging\LogRequestResponseService;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class LogResponseTest extends TestCase
{
    public function testItCallsLogRequestResponseService()
    {
        $request = new ServerRequest();
        $response = new EmptyResponse();

        $logRequestResponseService = $this->prophesize(LogRequestResponseService::class);
        $logRequestResponseService->logResponse($request, $response)->shouldBeCalled();

        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->process($request)->willReturn($response);

        $middleware = new LogResponse($logRequestResponseService->reveal());
        $middleware->process($request, $delegate->reveal());
    }
}
