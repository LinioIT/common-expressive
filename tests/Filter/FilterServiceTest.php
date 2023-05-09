<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Filter;

use Linio\Common\Laminas\Filter\FilterRulesFactory;
use Linio\Common\Laminas\Filter\FilterService;
use Linio\TestAssets\TestFilterRules;
use Linio\TestAssets\TestFilterRules2;
use Linio\TestAssets\TestFilterRules3;
use Particle\Filter\Filter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class FilterServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testItFiltersTheInput(): void
    {
        $input = ['key' => null];
        $filterRules = [TestFilterRules::class, TestFilterRules2::class];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(true);
        $container->get(TestFilterRules::class)->willReturn(new TestFilterRules());
        $container->get(TestFilterRules2::class)->willReturn(new TestFilterRules2());

        $filterClass = Filter::class;
        $filterFactory = new FilterRulesFactory($container->reveal());

        $service = new FilterService($filterClass, $filterFactory);
        $filteredInput = $service->filter($input, $filterRules);

        $this->assertSame('testtest2', $filteredInput['key']);
    }

    public function testItFiltersTheInputValuesInTheRules(): void
    {
        $input = ['key3' => 'part1', 'key4' => 'part2'];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(true);
        $container->get(TestFilterRules3::class)->willReturn(new TestFilterRules3());

        $filterClass = Filter::class;
        $filterFactory = new FilterRulesFactory($container->reveal());
        $filterRules = [TestFilterRules3::class];

        $service = new FilterService($filterClass, $filterFactory);
        $filteredInput = $service->filter($input, $filterRules);

        $this->assertSame('part1part2', $filteredInput['key3']);
    }
}
