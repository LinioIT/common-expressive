<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

use Eloquent\Phony\Phony;
use Linio\TestAssets\TestFilterRules;
use Linio\TestAssets\TestFilterRules2;
use Linio\TestAssets\TestFilterRules3;
use Particle\Filter\Filter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FilterServiceTest extends TestCase
{
    public function testItFiltersTheInput()
    {
        $input = ['key' => null];

        $container = Phony::mock(ContainerInterface::class);

        $filterClass = Filter::class;
        $filterFactory = new FilterRulesFactory($container->get());
        $filterRules = [TestFilterRules::class, TestFilterRules2::class];

        $service = new FilterService($filterClass, $filterFactory);
        $filteredInput = $service->filter($input, $filterRules);

        $this->assertSame('testtest2', $filteredInput['key']);
    }

    public function testItFiltersTheInputValuesInTheRules()
    {
        $input = ['key3' => 'part1', 'key4' => 'part2'];

        $container = Phony::mock(ContainerInterface::class);

        $filterClass = Filter::class;
        $filterFactory = new FilterRulesFactory($container->get());
        $filterRules = [TestFilterRules3::class];

        $service = new FilterService($filterClass, $filterFactory);
        $filteredInput = $service->filter($input, $filterRules);

        $this->assertSame('part1part2', $filteredInput['key3']);
    }
}
