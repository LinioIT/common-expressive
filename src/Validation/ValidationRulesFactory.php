<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Interop\Container\ContainerInterface;
use Linio\Common\Expressive\Exception\Base\NotFoundException;

class ValidationRulesFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws NotFoundException
     */
    public function make(string $validationClass): ValidationRules
    {
        if ($this->container->has($validationClass)) {
            return $this->container->get($validationClass);
        }

        if (!class_exists($validationClass)) {
            throw new NotFoundException(sprintf('Validation rules not found [%s]', $validationClass));
        }

        return new $validationClass();
    }
}
