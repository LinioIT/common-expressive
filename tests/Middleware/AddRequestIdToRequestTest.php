<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Middleware;

use Linio\Common\Expressive\Middleware\AddRequestIdToRequest;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class AddRequestIdToRequestTest extends TestCase
{
    public function testItAddsTheRequestIdAttributeUsingANewId()
    {
        $request = new ServerRequest();
        $response = new Response();
        $callable = function ($request, $response) {
            $this->assertNotNull($request->getAttribute('requestId'));

            return new EmptyResponse();
        };

        $middleware = new AddRequestIdToRequest();
        $middleware->__invoke($request, $response, $callable);
    }

    public function testItAddsTheRequestIdAttributeUsingTheHeader()
    {
        $requestId = 'testId';

        $request = (new ServerRequest())->withHeader('X-Request-Id', $requestId);
        $response = new Response();
        $callable = function ($request, $response) use ($requestId) {
            $this->assertSame($requestId, $request->getAttribute('requestId'));

            return new EmptyResponse();
        };

        $middleware = new AddRequestIdToRequest();
        $middleware->__invoke($request, $response, $callable);
    }
}
