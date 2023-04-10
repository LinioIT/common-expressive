<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Exception\Http;

use Linio\Common\Laminas\Exception\Base\ClientException;
use Linio\Common\Laminas\Exception\ExceptionTokens;

class ContentTypeNotSupportedException extends ClientException
{
    public function __construct(string $contentType = null)
    {
        $message = sprintf('Content-Type of [%s] is unsupported!', $contentType ?? 'unspecified');

        parent::__construct(ExceptionTokens::CONTENT_TYPE_NOT_SUPPORTED, ClientException::DEFAULT_STATUS_CODE, $message);
    }
}
