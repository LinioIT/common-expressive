<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValidatorFactoryTest extends TestCase
{
    public function testItGetsTheValidatorFromTheContainer()
    {
        $class = Validator::class;
        $validator = new $class();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(true)->shouldBeCalled();
        $container->get($class)->willReturn($validator)->shouldBeCalled();

        $factory = new ValidatorFactory($container->reveal(), $class);
        $actual = $factory->make($class);

        $this->assertSame($validator, $actual);
    }

    public function testItInstantiatesTheValidatorWhenItIsntInTheContainer()
    {
        $class = Validator::class;

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $factory = new ValidatorFactory($container->reveal(), $class);
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
    }

    public function testItFailsIfTheValidatorDoesntExist()
    {
        $class = 'InvalidClass';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidatorFactory($container->reveal(), $class);
        $factory->make($class);
    }
}
