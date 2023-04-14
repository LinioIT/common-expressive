<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Validation;

use Linio\Common\Laminas\Exception\Http\InvalidRequestException;
use Linio\Common\Laminas\Validation\ValidationRulesFactory;
use Linio\Common\Laminas\Validation\ValidationService;
use Linio\Common\Laminas\Validation\ValidatorFactory;
use Linio\TestAssets\TestValidationRules;
use Linio\TestAssets\TestValidationRules2;
use Linio\TestAssets\TestValidationRules3;
use Particle\Validator\ValidationResult;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ValidationServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testItValidatesAndPasses(): void
    {
        $input = ['key' => 'validValue', 'key2' => 'validValue'];
        $validationRule = [TestValidationRules::class, TestValidationRules2::class];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(true);
        $container->get(Validator::class)->willReturn(new Validator());
        $container->get(TestValidationRules::class)->willReturn(new TestValidationRules());
        $container->get(TestValidationRules2::class)->willReturn(new TestValidationRules2());

        $validatorResult = $this->prophesize(ValidationResult::class);
        $validatorResult
            ->isValid()
            ->willReturn(true);

        $validator = $this->prophesize(Validator::class);
        $validator
            ->validate($input)
            ->shouldBeCalled()
            ->willReturn($validatorResult);
        $validator
            ->required('key')
            ->shouldBeCalled()
            ->willReturn(false);
        $validator
            ->required('key2')
            ->shouldBeCalled()
            ->willReturn(false);

        $validatorFactory = $this->prophesize(ValidatorFactory::class);
        $validatorFactory
            ->make()
            ->willReturn($validator->reveal());

        $validationRulesFactory = new ValidationRulesFactory($container->reveal());

        $service = new ValidationService($validatorFactory->reveal(), $validationRulesFactory);
        $service->validate($input, $validationRule);
    }

    public function testItValidatesAndFailsWithASingleValidationRulesClass(): void
    {
        $input = ['invalidKey' => 'validValue'];
        $expectedErrors = [
            [
                'field' => 'key',
                'message' => 'key must be provided, but does not exist',
            ],
        ];

        $validationRules = [TestValidationRules::class];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(true);
        $container->get(Validator::class)->willReturn(new Validator());
        $container->get(TestValidationRules::class)->willReturn(new TestValidationRules());

        $validatorFactory = new ValidatorFactory($container->reveal(), Validator::class);
        $validationRulesFactory = new ValidationRulesFactory($container->reveal());

        $this->expectException(InvalidRequestException::class);

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        try {
            $service->validate($input, $validationRules);
        } catch (InvalidRequestException $exception) {
            $this->assertEquals($expectedErrors, $exception->getErrors());

            throw $exception;
        }
    }

    public function testItValidatesAndFailsUsingAMultipleValidationRulesClass(): void
    {
        $input = ['invalidKey' => 'validValue'];
        $validationRules = [TestValidationRules::class, TestValidationRules2::class];
        $expectedErrors = [
            [
                'field' => 'key',
                'message' => 'key must be provided, but does not exist',
            ],
            [
                'field' => 'key2',
                'message' => 'key2 must be provided, but does not exist',
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(true);
        $container->get(Validator::class)->willReturn(new Validator());
        $container->get(TestValidationRules::class)->willReturn(new TestValidationRules());
        $container->get(TestValidationRules2::class)->willReturn(new TestValidationRules2());

        $validatorFactory = new ValidatorFactory($container->reveal(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->reveal());

        $this->expectException(InvalidRequestException::class);

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        try {
            $service->validate($input, $validationRules);
        } catch (InvalidRequestException $exception) {
            $this->assertEquals($expectedErrors, $exception->getErrors());

            throw $exception;
        }
    }

    public function testItValidatesUsingInputValuesInTheRules(): void
    {
        $input = ['key3' => 'equalValue', 'key4' => 'equalValue'];
        $validationRules = [TestValidationRules3::class];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(Argument::any())->willReturn(true);
        $container->get(Validator::class)->willReturn(new Validator());
        $container->get(TestValidationRules3::class)->willReturn(new TestValidationRules3());

        $validatorFactory = new ValidatorFactory($container->reveal(), Validator::class);
        $validationRulesFactory = new ValidationRulesFactory($container->reveal());

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        $result = $service->validate($input, $validationRules);

        $this->assertNull($result);
    }
}
