<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Base;

class NonCriticalClientException extends NonCriticalDomainException
{
    public const DEFAULT_STATUS_CODE = 400;
}
