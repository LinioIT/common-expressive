<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Middleware;

use Linio\Common\Mezzio\Exception\Base\NonCriticalDomainException;
use Linio\Component\Microlog\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogExceptions implements MiddlewareInterface
{
    public const EXCEPTIONS_CHANNEL = 'exceptions';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            return $response;
        } catch (\Throwable $exception) {
            if ($exception instanceof NonCriticalDomainException) {
                Log::error($exception, [], self::EXCEPTIONS_CHANNEL);
            } else {
                Log::critical($exception, [], self::EXCEPTIONS_CHANNEL);
            }
            throw $exception;
        }
    }
}
