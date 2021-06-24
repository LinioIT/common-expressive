<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Base;

class ClientException extends DomainException
{
    public const DEFAULT_STATUS_CODE = 400;
}
