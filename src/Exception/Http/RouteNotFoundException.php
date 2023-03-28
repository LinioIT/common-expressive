<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Exception\Http;

use Linio\Common\Mezzio\Exception\Base\DomainException;
use Linio\Common\Mezzio\Exception\ExceptionTokens;

class RouteNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(ExceptionTokens::ROUTE_NOT_FOUND);
    }
}
