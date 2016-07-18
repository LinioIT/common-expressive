<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Filter;

use Eloquent\Phony\Phpunit\Phony;
use Interop\Container\ContainerInterface;
use Linio\TestAssets\TestFilterRules;
use Linio\TestAssets\TestFilterRules2;
use Particle\Filter\Filter;
use PHPUnit\Framework\TestCase;

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
}
