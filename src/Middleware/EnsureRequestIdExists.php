<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Middleware;

use Linio\Common\Mezzio\Exception\Http\MiddlewareOutOfOrderException;
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
