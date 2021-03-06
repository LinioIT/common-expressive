<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Container\ContainerInterface;
use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;

class ValidationRulesFactoryTest extends TestCase
{
    public function testItGetsTheValidationRulesFromTheContainer()
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

    public function testItInstantiatesTheValidationRulesWhenItIsntInTheContainer()
    {
        $class = TestValidationRules::class;

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $factory = new ValidationRulesFactory($container->get());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
        $container->get->never()->calledWith($class);
    }

    public function testItFailsIfTheValidationRulesClassDoesntExist()
    {
        $class = 'InvalidClass';

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidationRulesFactory($container->get());
        $factory->make($class);
    }
}
