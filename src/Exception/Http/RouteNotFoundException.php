<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Exception\Http;

use Linio\Common\Expressive\Exception\Base\DomainException;
use Linio\Common\Expressive\Exception\ExceptionTokens;

class RouteNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct(ExceptionTokens::ROUTE_NOT_FOUND);
    }
}
