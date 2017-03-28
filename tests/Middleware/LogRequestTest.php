<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Linio\Common\Expressive\Logging\LogRequestResponseService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class LogRequestTest extends TestCase
{
    public function testItCallsLogRequestResponseService()
    {
        $request = new ServerRequest();

        $logRequestResponseService = $this->prophesize(LogRequestResponseService::class);
        $logRequestResponseService->logRequest($request)->shouldBeCalled();

        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $middleware = new LogRequest($logRequestResponseService->reveal());
        $middleware->process($request, $delegate);
    }
}
