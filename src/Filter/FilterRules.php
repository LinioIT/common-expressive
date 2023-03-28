<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Filter;

use Particle\Filter\Filter;

interface FilterRules
{
    public function buildRules(Filter $filter, array $input);
}
