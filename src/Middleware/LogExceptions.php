<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Base\NonCriticalDomainException;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogExceptions
{
    public const EXCEPTIONS_CHANNEL = 'exceptions';

    /**
     * @param mixed $error
     */
    public function __invoke($error, ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if ($error instanceof NonCriticalDomainException) {
            Log::error($error, [], self::EXCEPTIONS_CHANNEL);
        } else {
            Log::critical($error, [], self::EXCEPTIONS_CHANNEL);
        }

        return $next($request, $response, $error);
    }
}
