<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Tests\Validation;

use Interop\Container\ContainerInterface;
use Linio\Common\Mezzio\Exception\Http\InvalidRequestException;
use Linio\Common\Mezzio\Validation\ValidationRulesFactory;
use Linio\Common\Mezzio\Validation\ValidationService;
use Linio\Common\Mezzio\Validation\ValidatorFactory;
use Linio\TestAssets\TestValidationRules;
use Linio\TestAssets\TestValidationRules2;
use Linio\TestAssets\TestValidationRules3;
use Particle\Validator\ValidationResult;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ValidationServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testItValidatesAndPasses()
    {
        $input = ['key' => 'validValue', 'key2' => 'validValue'];

        $container = $this->prophesize(ContainerInterface::class);

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
        $validationRule = [TestValidationRules::class, TestValidationRules2::class];

        $service = new ValidationService($validatorFactory->reveal(), $validationRulesFactory);
        $service->validate($input, $validationRule);
    }

    public function testItValidatesAndFailsWithASingleValidationRulesClass()
    {
        $input = ['invalidKey' => 'validValue'];
        $expectedErrors = [
            [
                'field' => 'key',
                'message' => 'key must be provided, but does not exist',
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);

        $validatorFactory = new ValidatorFactory($container->reveal(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->reveal());
        $validationRules = [TestValidationRules::class];

        $this->expectException(InvalidRequestException::class);

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        try {
            $service->validate($input, $validationRules);
        } catch (InvalidRequestException $exception) {
            $this->assertEquals($expectedErrors, $exception->getErrors());

            throw $exception;
        }
    }

    public function testItValidatesAndFailsUsingAMultipleValidationRulesClass()
    {
        $input = ['invalidKey' => 'validValue'];
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

        $validatorFactory = new ValidatorFactory($container->reveal(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->reveal());
        $validationRules = [TestValidationRules::class, TestValidationRules2::class];

        $this->expectException(InvalidRequestException::class);

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        try {
            $service->validate($input, $validationRules);
        } catch (InvalidRequestException $exception) {
            $this->assertEquals($expectedErrors, $exception->getErrors());

            throw $exception;
        }
    }

    public function testItValidatesUsingInputValuesInTheRules()
    {
        $input = ['key3' => 'equalValue', 'key4' => 'equalValue'];

        $container = $this->prophesize(ContainerInterface::class);

        $validatorFactory = new ValidatorFactory($container->reveal(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->reveal());
        $validationRules = [TestValidationRules3::class];

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        $result = $service->validate($input, $validationRules);

        $this->assertNull($result);
    }
}
