<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Interop\Container\ContainerInterface;
use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Particle\Validator\Validator;

class ValidatorFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $validatorClass;

    /**
     * @param ContainerInterface $container
     * @param string $validatorClass
     */
    public function __construct(ContainerInterface $container, string $validatorClass)
    {
        $this->container = $container;
        $this->validatorClass = $validatorClass;
    }

    /**
     * @return Validator
     */
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
