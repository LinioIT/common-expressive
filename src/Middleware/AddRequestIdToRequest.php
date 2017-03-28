<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddRequestIdToRequest implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $requestId = $request->hasHeader('X-Request-ID') ? $request->getHeader('X-Request-ID')[0] : uniqid('b4a');
        $request = $request->withAttribute('requestId', $requestId);

        return $delegate->process($request);
    }
}
