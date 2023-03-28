<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Exception\Base;

use Linio\Common\Mezzio\Exception\ExceptionTokens;

class NotFoundException extends ClientException
{
    public function __construct(string $message = ExceptionTokens::ENTITY_NOT_FOUND, array $errors = [])
    {
        parent::__construct(ExceptionTokens::ENTITY_NOT_FOUND, ClientException::DEFAULT_STATUS_CODE, $message, $errors);
    }
}
