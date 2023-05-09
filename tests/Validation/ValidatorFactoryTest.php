<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Validation;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Linio\Common\Laminas\Validation\ValidatorFactory;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ValidatorFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testItGetsTheValidatorFromTheContainer(): void
    {
        $class = Validator::class;
        $validator = new $class();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(true);
        $container->get($class)->willReturn($validator);

        $factory = new ValidatorFactory($container->reveal(), $class);
        $actual = $factory->make();

        $this->assertSame($validator, $actual);
    }

    public function testItInstantiatesTheValidatorWhenItIsNotInTheContainer(): void
    {
        $class = Validator::class;

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $factory = new ValidatorFactory($container->reveal(), $class);
        $actual = $factory->make();

        $this->assertInstanceOf($class, $actual);
    }

    public function testItFailsIfTheValidatorDoesntExist(): void
    {
        $class = 'InvalidClass';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidatorFactory($container->reveal(), $class);
        $factory->make();
    }
}
