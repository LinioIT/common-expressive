<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddRequestIdToResponse implements MiddlewareInterface
{
    use EnsureRequestIdExists;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->ensureRequestIdExists($request);
        $response = $delegate->process($request);

        return $response->withHeader('X-Request-ID', $request->getAttribute('requestId'));
    }
}
