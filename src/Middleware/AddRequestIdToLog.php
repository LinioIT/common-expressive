<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddRequestIdToLog implements MiddlewareInterface
{
    use EnsureRequestIdExists;

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->ensureRequestIdExists($request);

        Log::addGlobalContext('requestId', $request->getAttribute('requestId'));

        return $delegate->process($request);
    }
}
