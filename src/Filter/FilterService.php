<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Filter;

use Particle\Filter\Filter;

class FilterService
{
    private string $filterClass;
    private FilterRulesFactory $filterRulesFactory;

    public function __construct(string $filterClass, FilterRulesFactory $filterRulesFactory)
    {
        $this->filterClass = $filterClass;
        $this->filterRulesFactory = $filterRulesFactory;
    }

    public function filter(array $input, array $filterRulesClasses): array
    {
        if (empty($filterRulesClasses)) {
            return $input;
        }

        /** @var Filter $filter */
        $filter = new $this->filterClass();

        foreach ($filterRulesClasses as $filterRulesClass) {
            $filterRules = $this->filterRulesFactory->make($filterRulesClass);
            $filterRules->buildRules($filter, $input);
        }

        return $filter->filter($input);
    }
}
