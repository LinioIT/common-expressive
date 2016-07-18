<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

use Particle\Filter\Filter;

class FilterService
{
    /**
     * @var string
     */
    private $filterClass;

    /**
     * @var FilterRulesFactory
     */
    private $filterRulesFactory;

    /**
     * @param string $filterClass
     * @param FilterRulesFactory $filterRulesFactory
     */
    public function __construct(string $filterClass, FilterRulesFactory $filterRulesFactory)
    {
        $this->filterClass = $filterClass;
        $this->filterRulesFactory = $filterRulesFactory;
    }

    /**
     * @param array $input
     * @param array $filterRulesClasses
     *
     * @return array
     */
    public function filter(array $input, array $filterRulesClasses): array
    {
        if (empty($filterRulesClasses)) {
            return $input;
        }

        /** @var Filter $filter */
        $filter = new $this->filterClass();

        foreach ($filterRulesClasses as $filterRulesClass) {
            $filterRules = $this->filterRulesFactory->make($filterRulesClass);
            $filterRules->buildRules($filter);
        }

        return $filter->filter($input);
    }
}
