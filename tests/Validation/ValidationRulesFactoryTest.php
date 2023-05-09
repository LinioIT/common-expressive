<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Validation;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Linio\Common\Laminas\Validation\ValidationRulesFactory;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ValidationRulesFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testItGetsTheValidationRulesFromTheContainer(): void
    {
        $class = TestValidationRules::class;
        $testValidationRules = new $class();

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has($class)
            ->shouldBeCalled()
            ->willReturn(true);
        $container
            ->get($class)
            ->shouldBeCalled()
            ->willReturn($testValidationRules);

        $factory = new ValidationRulesFactory($container->reveal());
        $actual = $factory->make($class);

        $this->assertSame($testValidationRules, $actual);
    }

    public function testItInstantiatesTheValidationRulesWhenItIsntInTheContainer(): void
    {
        $class = TestValidationRules::class;

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has($class)
            ->shouldBeCalled()
            ->willReturn(false);
        $container
            ->get($class)
            ->shouldNotBeCalled();

        $factory = new ValidationRulesFactory($container->reveal());
        $actual = $factory->make($class);

        $this->assertInstanceOf($class, $actual);
    }

    public function testItFailsIfTheValidationRulesClassDoesntExist(): void
    {
        $class = 'InvalidClass';

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has($class)
            ->willReturn(false);

        $this->expectException(NotFoundException::class);

        $factory = new ValidationRulesFactory($container->reveal());
        $factory->make($class);
    }
}
