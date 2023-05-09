<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddRequestIdToResponse implements MiddlewareInterface
{
    use EnsureRequestIdExists;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->ensureRequestIdExists($request);

        /** @var ResponseInterface $response */
        $response = $handler->handle($request);
        return $response->withHeader('X-Request-ID', $request->getAttribute('requestId'));
    }
}
