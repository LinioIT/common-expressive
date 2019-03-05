<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Eloquent\Phony\Phony;
use Linio\Common\Expressive\Exception\Http\InvalidRequestException;
use Linio\TestAssets\TestValidationRules;
use Linio\TestAssets\TestValidationRules2;
use Linio\TestAssets\TestValidationRules3;
use Particle\Validator\ValidationResult;
use Particle\Validator\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValidationServiceTest extends TestCase
{
    public function testItValidatesAndPasses(): void
    {
        $input = ['key' => 'validValue', 'key2' => 'validValue'];

        $container = Phony::mock(ContainerInterface::class);

        $validatorResult = Phony::mock(ValidationResult::class);
        $validatorResult->isValid->returns(true);

        $validator = Phony::mock(Validator::class);
        $validator->validate->with($input)->returns($validatorResult);

        $validatorFactory = Phony::mock(ValidatorFactory::class);
        $validatorFactory->make->returns($validator->get());

        $validationRulesFactory = new ValidationRulesFactory($container->get());
        $validationRule = [TestValidationRules::class, TestValidationRules2::class];

        $service = new ValidationService($validatorFactory->get(), $validationRulesFactory);
        $service->validate($input, $validationRule);

        $validator->validate->called();
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

        $container = Phony::mock(ContainerInterface::class);

        $validatorFactory = new ValidatorFactory($container->get(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->get());
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

    public function testItValidatesAndFailsUsingAMultipleValidationRulesClass(): void
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

        $container = Phony::mock(ContainerInterface::class);

        $validatorFactory = new ValidatorFactory($container->get(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->get());
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

    public function testItValidatesUsingInputValuesInTheRules(): void
    {
        $input = ['key3' => 'equalValue', 'key4' => 'equalValue'];

        $container = Phony::mock(ContainerInterface::class);

        $validatorFactory = new ValidatorFactory($container->get(), Validator::class);

        $validationRulesFactory = new ValidationRulesFactory($container->get());
        $validationRules = [TestValidationRules3::class];

        $service = new ValidationService($validatorFactory, $validationRulesFactory);

        $result = $service->validate($input, $validationRules);

        $this->assertNull($result);
    }
}
