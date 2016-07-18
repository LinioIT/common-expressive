<?php

declare(strict_types=1);

namespace Linio\TestAssets;

use Linio\Common\Expressive\Filter\FilterRules;
use Particle\Filter\Filter;

class TestFilterRules2 implements FilterRules
{
    /**
     * @param Filter $filter
     */
    public function buildRules(Filter $filter)
    {
        $filter->value('key')->append('test2');
    }
}
