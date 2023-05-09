<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Validation;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Particle\Validator\Validator;
use Psr\Container\ContainerInterface;

class ValidatorFactory
{
    private ContainerInterface $container;

    private string $validatorClass;

    public function __construct(ContainerInterface $container, string $validatorClass)
    {
        $this->container = $container;
        $this->validatorClass = $validatorClass;
    }

    public function make(): Validator
    {
        if ($this->container->has($this->validatorClass)) {
            return $this->container->get($this->validatorClass);
        }

        if (!class_exists($this->validatorClass)) {
            throw new NotFoundException(sprintf('Validator not found [%s]', $this->validatorClass));
        }

        return new $this->validatorClass();
    }
}
