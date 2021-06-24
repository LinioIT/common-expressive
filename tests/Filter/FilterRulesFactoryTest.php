<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Filter;

use Interop\Container\ContainerInterface;
use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Linio\Common\Expressive\Filter\FilterRulesFactory;
use Linio\TestAssets\TestFilterRules;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FilterRulesFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testItGetsTheFilterRulesFromTheContainer()
    {
        $class = TestFilterRules::class;
        $testFilterRules = new $class();

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has($class)
            ->shouldBeCalled()
            ->willReturn(true);
        $container
            ->get($class)
            ->shouldBeCalled()
            ->willReturn($testFilterRules);

        $factory = new FilterRulesFactory($container->reveal());
        $factory->make($class);
    }

    public function testItInstantiatesTheFilterRulesWhenItIsntInTheContainer()
    {
        $class = TestFilterRules::class;

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has($class)
            ->shouldBeCalled()
            ->willReturn(false);
        $container
            ->get($class)
            ->shouldNotBeCalled();

        $factory = new FilterRulesFactory($container->reveal());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
    }

    public function testItFailsIfTheFilterRulesClassDoesntExist()
    {
        $class = 'InvalidClass';

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has($class)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->expectException(NotFoundException::class);

        $factory = new FilterRulesFactory($container->reveal());
        $factory->make($class);
    }
}
