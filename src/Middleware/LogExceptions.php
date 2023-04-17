<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Base\NonCriticalDomainException;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogExceptions
{
    public const EXCEPTIONS_CHANNEL = 'exceptions';

    public function __invoke(mixed $error, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($error instanceof NonCriticalDomainException) {
            Log::error($error, [], self::EXCEPTIONS_CHANNEL);
        } else {
            Log::critical($error, [], self::EXCEPTIONS_CHANNEL);
        }

        return $response;
    }
}
