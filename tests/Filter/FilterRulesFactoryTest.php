<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Filter;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Linio\Common\Laminas\Filter\FilterRulesFactory;
use Linio\TestAssets\TestFilterRules;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class FilterRulesFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testItGetsTheFilterRulesFromTheContainer(): void
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

    public function testItInstantiatesTheFilterRulesWhenItIsNotInTheContainer(): void
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

    public function testItFailsIfTheFilterRulesClassDoesntExist(): void
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
