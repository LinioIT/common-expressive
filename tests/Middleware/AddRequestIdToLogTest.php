<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class AddRequestIdToLogTest extends TestCase
{
    public function testItFailsAddingAGlobalContextWithoutARequestId()
    {
        $request = new ServerRequest();
        $delegate = $this->prophesize(DelegateInterface::class);

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new AddRequestIdToLog();
        $middleware->process($request, $delegate->reveal());
    }
}
