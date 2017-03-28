<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Logging;

use Linio\Common\Expressive\Exception\Base\NonCriticalDomainException;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class LogExceptions
{
    const EXCEPTIONS_CHANNEL = 'exceptions';

    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response): void
    {
        if ($error instanceof NonCriticalDomainException) {
            Log::error($error, [], self::EXCEPTIONS_CHANNEL);
        } else {
            Log::critical($error, [], self::EXCEPTIONS_CHANNEL);
        }
    }
}
