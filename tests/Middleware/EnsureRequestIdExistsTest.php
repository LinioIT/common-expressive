<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Middleware;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Expressive\Middleware\EnsureRequestIdExists;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Zend\Diactoros\ServerRequest;

class EnsureRequestIdExistsTest extends TestCase
{
    use EnsureRequestIdExists;
    use ProphecyTrait;

    public function testItDoesNothingWhenARequestIdExists()
    {
        $request = $this->prophesize(ServerRequest::class);
        $request
            ->getAttribute('requestId', false)
            ->shouldBeCalled()
            ->willReturn('someId');

        $this->ensureRequestIdExists($request->reveal());
    }

    public function testItFailsWhenARequestIdDoesntExist()
    {
        $request = new ServerRequest();

        $this->expectException(MiddlewareOutOfOrderException::class);

        $this->ensureRequestIdExists($request);
    }
}
