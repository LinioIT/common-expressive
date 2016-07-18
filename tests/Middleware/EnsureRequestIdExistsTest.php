<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class EnsureRequestIdExistsTest extends TestCase
{
    use EnsureRequestIdExists;

    public function testItDoesNothingWhenARequestIdExists()
    {
        $request = (new ServerRequest())->withAttribute('requestId', 'someId');

        $actual = $this->ensureRequestIdExists($request);

        $this->assertNull($actual);
    }

    public function testItFailsWhenARequestIdDoesntExist()
    {
        $request = new ServerRequest();

        $this->expectException(MiddlewareOutOfOrderException::class);

        $this->ensureRequestIdExists($request);
    }
}
