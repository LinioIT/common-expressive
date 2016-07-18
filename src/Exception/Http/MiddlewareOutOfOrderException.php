<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Http;

use Linio\Common\Expressive\Exception\Base\DomainException;
use Linio\Common\Expressive\Exception\ExceptionTokens;

class MiddlewareOutOfOrderException extends DomainException
{
    public function __construct(string $previousMiddlewareClass, string $currentMiddlewareClass)
    {
        $message = sprintf(
            'Middleware order is incorrect. [%s] must come before [%s] in the middleware pipeline!',
            $previousMiddlewareClass,
            $currentMiddlewareClass
        );

        parent::__construct(
            ExceptionTokens::MIDDLEWARE_RUN_OUT_OF_ORDER,
            DomainException::DEFAULT_STATUS_CODE,
            $message
        );
    }
}
