<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Tests\Middleware;

use Linio\Common\Mezzio\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Mezzio\Middleware\EnsureRequestIdExists;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Laminas\Diactoros\ServerRequest;

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
