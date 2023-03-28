<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Exception\Http;

use Linio\Common\Mezzio\Exception\Base\ClientException;
use Linio\Common\Mezzio\Exception\ExceptionTokens;

class ContentTypeNotSupportedException extends ClientException
{
    public function __construct(string $contentType = null)
    {
        $message = sprintf('Content-Type of [%s] is unsupported!', $contentType ?? 'unspecified');

        parent::__construct(ExceptionTokens::CONTENT_TYPE_NOT_SUPPORTED, ClientException::DEFAULT_STATUS_CODE, $message);
    }
}
