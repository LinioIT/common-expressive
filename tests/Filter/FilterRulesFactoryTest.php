<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

use Eloquent\Phony\Phony;
use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Linio\TestAssets\TestFilterRules;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FilterRulesFactoryTest extends TestCase
{
    public function testItGetsTheFilterRulesFromTheContainer()
    {
        $class = TestFilterRules::class;
        $testFilterRules = new $class();

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(true);
        $container->get->with($class)->returns($testFilterRules);

        $factory = new FilterRulesFactory($container->get());
        $factory->make($class);

        $container->has->calledWith($class);
        $container->get->calledWith($class);
    }

    public function testItInstantiatesTheFilterRulesWhenItIsntInTheContainer()
    {
        $class = TestFilterRules::class;

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $factory = new FilterRulesFactory($container->get());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
        $container->get->never()->calledWith($class);
    }

    public function testItFailsIfTheFilterRulesClassDoesntExist()
    {
        $class = 'InvalidClass';

        $container = Phony::mock(ContainerInterface::class);
        $container->has->with($class)->returns(false);

        $this->expectException(NotFoundException::class);

        $factory = new FilterRulesFactory($container->get());
        $factory->make($class);
    }
}
