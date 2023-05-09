<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Exception\Http;

use Linio\Common\Laminas\Exception\Base\ClientException;
use Linio\Common\Laminas\Exception\ExceptionTokens;

class InvalidRequestException extends ClientException
{
    public function __construct(array $errors, string $message = ExceptionTokens::INVALID_REQUEST)
    {
        parent::__construct(ExceptionTokens::INVALID_REQUEST, ClientException::DEFAULT_STATUS_CODE, $message, $errors);
    }
}
