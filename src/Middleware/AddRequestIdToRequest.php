<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddRequestIdToRequest
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $requestId = $request->hasHeader('X-Request-ID') ? $request->getHeader('X-Request-ID')[0] : uniqid('b4a');

        $request = $request->withAttribute('requestId', $requestId);

        return $next($request, $response);
    }
}
