<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Middleware\EnsureRequestIdExists;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EnsureRequestIdExistsTest extends TestCase
{
    use EnsureRequestIdExists;
    use ProphecyTrait;

    public function testItDoesNothingWhenARequestIdExists(): void
    {
        $request = $this->prophesize(ServerRequest::class);
        $request
            ->getAttribute('requestId', false)
            ->shouldBeCalled()
            ->willReturn('someId');

        $this->ensureRequestIdExists($request->reveal());
    }

    public function testItFailsWhenARequestIdDoesntExist(): void
    {
        $request = new ServerRequest();

        $this->expectException(MiddlewareOutOfOrderException::class);

        $this->ensureRequestIdExists($request);
    }
}
