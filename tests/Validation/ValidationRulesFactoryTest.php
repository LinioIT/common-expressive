<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Eloquent\Phony\Phony;
use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValidationRulesFactoryTest extends TestCase
{
    public function testItGetsTheValidationRulesFromTheContainer(): void
    {
        $class = TestValidationRules::class;
        $testValidationRules = new $class();

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(true);
        $container->get->with($class)->returns($testValidationRules);

        $factory = new ValidationRulesFactory($container->get());
        $actual = $factory->make($class);

        $this->assertSame($testValidationRules, $actual);
        $container->has->calledWith($class);
        $container->get->calledWith($class);
    }

    public function testItInstantiatesTheValidationRulesWhenItIsntInTheContainer(): void
    {
        $class = TestValidationRules::class;

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $factory = new ValidationRulesFactory($container->get());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
        $container->get->never()->calledWith($class);
    }

    public function testItFailsIfTheValidationRulesClassDoesntExist(): void
    {
        $class = 'InvalidClass';

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidationRulesFactory($container->get());
        $factory->make($class);
    }
}
