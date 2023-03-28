<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Tests\Middleware;

use Linio\Common\Mezzio\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Mezzio\Middleware\AddRequestIdToLog;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;

class AddRequestIdToLogTest extends TestCase
{
    public function testItFailsAddingAGlobalContextWithoutARequestId()
    {
        $request = new ServerRequest();
        $response = new Response();
        $callable = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new AddRequestIdToLog();
        $middleware->__invoke($request, $response, $callable);
    }
}
