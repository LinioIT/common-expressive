<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Exception\Http;

use Linio\Common\Laminas\Exception\Base\DomainException;
use Linio\Common\Laminas\Exception\ExceptionTokens;

class RouteNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(ExceptionTokens::ROUTE_NOT_FOUND);
    }
}
