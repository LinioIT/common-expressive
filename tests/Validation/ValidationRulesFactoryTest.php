<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValidationRulesFactoryTest extends TestCase
{
    public function testItGetsTheValidationRulesFromTheContainer()
    {
        $class = TestValidationRules::class;
        $testValidationRules = new $class();

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(true)->shouldBeCalled();
        $container->get($class)->willReturn($testValidationRules)->shouldBeCalled();

        $factory = new ValidationRulesFactory($container->reveal());
        $actual = $factory->make($class);

        $this->assertSame($testValidationRules, $actual);
    }

    public function testItInstantiatesTheValidationRulesWhenItIsntInTheContainer()
    {
        $class = TestValidationRules::class;

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $factory = new ValidationRulesFactory($container->reveal());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
    }

    public function testItFailsIfTheValidationRulesClassDoesntExist()
    {
        $class = 'InvalidClass';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidationRulesFactory($container->reveal());
        $factory->make($class);
    }
}
