<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Eloquent\Phony\Phpunit\Phony;
use Linio\Common\Expressive\Logging\LogRequestResponseService;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class LogRequestTest extends TestCase
{
    public function testItCallsLogRequestResponseService()
    {
        $logRequestResponseService = Phony::mock(LogRequestResponseService::class);

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request, $response) {
            return new EmptyResponse();
        };

        $middleware = new LogRequest($logRequestResponseService->get());
        $middleware->__invoke($request, $response, $next);

        $logRequestResponseService->logRequest->calledWith($request);
    }
}
