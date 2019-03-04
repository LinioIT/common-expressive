<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Base;

use DomainException as SplDomainException;
use Exception;
use Linio\Common\Expressive\Exception\ExceptionTokens;

class DomainException extends SplDomainException
{
    const DEFAULT_STATUS_CODE = 500;

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param int $statusCode
     * @param array $errors An array of arrays containing either or both keys "field" and "message"
     * @param Exception $previous
     */
    public function __construct(
        string $token,
        int $statusCode = null,
        string $message = ExceptionTokens::AN_ERROR_HAS_OCCURRED,
        array $errors = [],
        Exception $previous = null
    ) {
        $this->token = $token;
        $this->errors = $errors;
        $statusCode = $statusCode ?? static::DEFAULT_STATUS_CODE;

        parent::__construct($message, $statusCode, $previous);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
