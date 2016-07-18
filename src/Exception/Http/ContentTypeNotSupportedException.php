<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Http;

use Linio\Common\Expressive\Exception\Base\ClientException;
use Linio\Common\Expressive\Exception\ExceptionTokens;

class ContentTypeNotSupportedException extends ClientException
{
    public function __construct(string $contentType = 'unspecified')
    {
        $message = sprintf('Content-Type of [%s] is unsupported!', $contentType);

        parent::__construct(ExceptionTokens::CONTENT_TYPE_NOT_SUPPORTED, ClientException::DEFAULT_STATUS_CODE, $message);
    }
}
