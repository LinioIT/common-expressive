<?php

declare(strict_types=1);

namespace Linio\TestAssets;

use Linio\Common\Laminas\Filter\FilterRules;
use Particle\Filter\Filter;

class TestFilterRules3 implements FilterRules
{
    public function buildRules(Filter $filter, array $input)
    {
        $filter->value('key3')->append($input['key4']);
    }
}
