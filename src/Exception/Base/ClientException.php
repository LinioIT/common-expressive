<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Exception\Base;

class ClientException extends DomainException
{
    public const DEFAULT_STATUS_CODE = 400;
}
