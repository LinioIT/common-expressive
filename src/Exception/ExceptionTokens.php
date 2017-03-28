<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception;

class ExceptionTokens
{
    const INVALID_REQUEST = 'INVALID_REQUEST';
    const ENTITY_NOT_FOUND = 'ENTITY_NOT_FOUND';
    const RUNTIME_EXCEPTION = 'RUNTIME_EXCEPTION';
    const AN_ERROR_HAS_OCCURRED = 'AN_ERROR_HAS_OCCURRED';
    const MIDDLEWARE_RUN_OUT_OF_ORDER = 'MIDDLEWARE_RUN_OUT_OF_ORDER';
}
