<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Eloquent\Phony\Phpunit\Phony;
use Linio\Common\Expressive\Logging\LogRequestResponseService;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class LogResponseTest extends TestCase
{
    public function testItCallsLogRequestResponseService()
    {
        $logRequestResponseService = Phony::mock(LogRequestResponseService::class);

        $request = new ServerRequest();
        $response = new Response();
        $next = function ($request, $response) {
            return $response;
        };

        $middleware = new LogResponse($logRequestResponseService->get());
        $middleware->__invoke($request, $response, $next);

        $logRequestResponseService->logResponse->calledWith($request, $response);
    }
}
