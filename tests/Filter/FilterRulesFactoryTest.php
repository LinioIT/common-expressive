<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

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

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(true)->shouldBeCalled();
        $container->get($class)->willReturn($testFilterRules)->shouldBeCalled();

        $factory = new FilterRulesFactory($container->reveal());
        $factory->make($class);
    }

    public function testItInstantiatesTheFilterRulesWhenItIsntInTheContainer()
    {
        $class = TestFilterRules::class;

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $factory = new FilterRulesFactory($container->reveal());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
    }

    public function testItFailsIfTheFilterRulesClassDoesntExist()
    {
        $class = 'InvalidClass';

        $container = $this->prophesize(ContainerInterface::class);
        $container->has($class)->willReturn(false);

        $this->expectException(NotFoundException::class);

        $factory = new FilterRulesFactory($container->reveal());
        $factory->make($class);
    }
}
