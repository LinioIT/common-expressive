<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddRequestIdToRequest implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $request->hasHeader('X-Request-ID') ? $request->getHeader('X-Request-ID')[0] : uniqid('b4a');

        $request = $request->withAttribute('requestId', $requestId);

        return $handler->handle($request);
    }
}
