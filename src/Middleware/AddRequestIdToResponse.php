<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddRequestIdToResponse
{
    use EnsureRequestIdExists;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->ensureRequestIdExists($request);

        /** @var ResponseInterface $response */
        $response = $next($request, $response);

        return $response->withHeader('X-Request-ID', $request->getAttribute('requestId'));
    }
}
