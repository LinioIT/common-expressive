<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

use Interop\Container\ContainerInterface;
use Linio\Common\Expressive\Exception\Base\NotFoundException;

class FilterRulesFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $filterRulesClass
     *
     * @throws NotFoundException
     *
     * @return FilterRules
     */
    public function make(string $filterRulesClass): FilterRules
    {
        if ($this->container->has($filterRulesClass)) {
            return $this->container->get($filterRulesClass);
        }

        if (!class_exists($filterRulesClass)) {
            throw new NotFoundException(sprintf('Filter rules not found [%s]', $filterRulesClass));
        }

        return new $filterRulesClass();
    }
}
