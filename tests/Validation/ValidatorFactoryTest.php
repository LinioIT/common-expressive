<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Eloquent\Phony\Phony;
use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValidatorFactoryTest extends TestCase
{
    public function testItGetsTheValidatorFromTheContainer(): void
    {
        $class = Validator::class;
        $validator = new $class();

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(true);
        $container->get->with($class)->returns($validator);

        $factory = new ValidatorFactory($container->get(), $class);
        $actual = $factory->make($class);

        $this->assertSame($validator, $actual);
        $container->has->calledWith($class);
        $container->get->calledWith($class);
    }

    public function testItInstantiatesTheValidatorWhenItIsntInTheContainer(): void
    {
        $class = Validator::class;

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $factory = new ValidatorFactory($container->get(), $class);
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
        $container->get->never()->calledWith($class);
    }

    public function testItFailsIfTheValidatorDoesntExist(): void
    {
        $class = 'InvalidClass';

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidatorFactory($container->get(), $class);
        $factory->make($class);
    }
}
