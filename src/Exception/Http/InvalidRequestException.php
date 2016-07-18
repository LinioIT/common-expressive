<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Http;

use Linio\Common\Expressive\Exception\Base\ClientException;
use Linio\Common\Expressive\Exception\ExceptionTokens;

class InvalidRequestException extends ClientException
{
    public function __construct(array $errors, string $message = ExceptionTokens::INVALID_REQUEST)
    {
        parent::__construct(ExceptionTokens::INVALID_REQUEST, ClientException::DEFAULT_STATUS_CODE, $message, $errors);
    }
}
