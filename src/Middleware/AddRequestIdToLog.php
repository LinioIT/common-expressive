<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddRequestIdToLog
{
    use EnsureRequestIdExists;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->ensureRequestIdExists($request);

        Log::addGlobalContext('requestId', $request->getAttribute('requestId'));

        return $next($request, $response);
    }
}
