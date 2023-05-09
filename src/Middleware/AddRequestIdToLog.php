<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddRequestIdToLog implements MiddlewareInterface
{
    use EnsureRequestIdExists;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->ensureRequestIdExists($request);

        Log::addGlobalContext('requestId', $request->getAttribute('requestId'));

        return $handler->handle($request);
    }
}
