<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Psr\Http\Message\ServerRequestInterface;

trait EnsureRequestIdExists
{
    public function ensureRequestIdExists(ServerRequestInterface $request)
    {
        if (!$request->getAttribute('requestId', false)) {
            throw new MiddlewareOutOfOrderException(AddRequestIdToRequest::class, self::class);
        }
    }
}
