<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class AddRequestIdToRequestTest extends TestCase
{
    public function testItAddsTheRequestIdAttributeUsingANewId()
    {
        $request = new ServerRequest();
        $delegate = new class($this) implements DelegateInterface {
            public function __construct(TestCase $testCase)
            {
                $this->testCase = $testCase;
            }

            public function process(ServerRequestInterface $request)
            {
                $this->testCase->assertNotNull($request->getAttribute('requestId'));

                return new EmptyResponse();
            }
        };

        $middleware = new AddRequestIdToRequest();
        $middleware->process($request, $delegate);
    }

    public function testItAddsTheRequestIdAttributeUsingTheHeader()
    {
        $request = (new ServerRequest())->withHeader('X-Request-Id', 'testId');
        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->process(Argument::type(ServerRequestInterface::class))->willReturn(new EmptyResponse());

        $middleware = new AddRequestIdToRequest();
        $middleware->process($request, $delegate->reveal());

        $delegate->process($request->withAttribute('requestId', 'testId'))->shouldHaveBeenCalled();
    }
}
