<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Validation;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Container\ContainerInterface;
use Linio\Common\Mezzio\Exception\Base\NotFoundException;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorFactoryTest extends TestCase
{
    public function testItGetsTheValidatorFromTheContainer()
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

    public function testItInstantiatesTheValidatorWhenItIsntInTheContainer()
    {
        $class = Validator::class;

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $factory = new ValidatorFactory($container->get(), $class);
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
        $container->get->never()->calledWith($class);
    }

    public function testItFailsIfTheValidatorDoesntExist()
    {
        $class = 'InvalidClass';

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidatorFactory($container->get(), $class);
        $factory->make($class);
    }
}
