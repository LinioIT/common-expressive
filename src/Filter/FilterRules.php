<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

use Particle\Filter\Filter;

interface FilterRules
{
    /**
     * @param Filter $filter
     * @param array $input
     */
    public function buildRules(Filter $filter, array $input = []);
}
