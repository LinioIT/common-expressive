<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Filter;

use Interop\Container\ContainerInterface;
use Linio\Common\Expressive\Filter\FilterRulesFactory;
use Linio\Common\Expressive\Filter\FilterService;
use Linio\TestAssets\TestFilterRules;
use Linio\TestAssets\TestFilterRules2;
use Linio\TestAssets\TestFilterRules3;
use Particle\Filter\Filter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FilterServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testItFiltersTheInput()
    {
        $input = ['key' => null];

        $container = $this->prophesize(ContainerInterface::class);

        $filterClass = Filter::class;
        $filterFactory = new FilterRulesFactory($container->reveal());
        $filterRules = [TestFilterRules::class, TestFilterRules2::class];

        $service = new FilterService($filterClass, $filterFactory);
        $filteredInput = $service->filter($input, $filterRules);

        $this->assertSame('testtest2', $filteredInput['key']);
    }

    public function testItFiltersTheInputValuesInTheRules()
    {
        $input = ['key3' => 'part1', 'key4' => 'part2'];

        $container = $this->prophesize(ContainerInterface::class);

        $filterClass = Filter::class;
        $filterFactory = new FilterRulesFactory($container->reveal());
        $filterRules = [TestFilterRules3::class];

        $service = new FilterService($filterClass, $filterFactory);
        $filteredInput = $service->filter($input, $filterRules);

        $this->assertSame('part1part2', $filteredInput['key3']);
    }
}
