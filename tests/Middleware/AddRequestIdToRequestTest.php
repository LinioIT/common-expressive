<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Middleware\AddRequestIdToRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddRequestIdToRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testItAddsTheRequestIdAttributeUsingANewId(): void
    {
        $request = new ServerRequest();
        $handler = new class() implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $middleware = new AddRequestIdToRequest();
        $response = $middleware->process($request, $handler);

        $this->assertNotNull($response);

        $this->assertNotNull($response->getHeaderLine('X-Request-ID'));
    }

    public function testItAddsTheRequestIdAttributeUsingTheHeader(): void
    {
        $request = new ServerRequest([], [], null, null, '1.1', ['X-Request-ID' => 'existing-id']);
        $handler = new class() implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new EmptyResponse();

                return $response->withHeader('X-Request-ID', $request->getHeaderLine('X-Request-ID'));
            }
        };

        $middleware = new AddRequestIdToRequest();
        $response = $middleware->process($request, $handler);

        $this->assertNotNull($response);
        $this->assertSame('existing-id', $response->getHeaderLine('X-Request-ID'));
    }
}
