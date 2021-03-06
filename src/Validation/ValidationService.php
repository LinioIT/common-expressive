<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Linio\Common\Expressive\Exception\Base\NotFoundException;
use Linio\Common\Expressive\Exception\Http\InvalidRequestException;
use Particle\Validator\Validator;

class ValidationService
{
    /**
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var ValidationRulesFactory
     */
    private $validationRulesFactory;

    /**
     * @param ValidatorFactory $validatorFactory
     * @param ValidationRulesFactory $validationRulesFactory
     */
    public function __construct(ValidatorFactory $validatorFactory, ValidationRulesFactory $validationRulesFactory)
    {
        $this->validatorFactory = $validatorFactory;
        $this->validationRulesFactory = $validationRulesFactory;
    }

    /**
     * @param array $input
     * @param array $validationRulesClasses
     *
     * @throws NotFoundException
     * @throws InvalidRequestException
     */
    public function validate(array $input, array $validationRulesClasses)
    {
        if (empty($validationRulesClasses)) {
            return;
        }

        $validator = $this->validatorFactory->make();

        foreach ($validationRulesClasses as $validationRulesClass) {
            $validationRules = $this->validationRulesFactory->make($validationRulesClass);
            $validationRules->buildRules($validator, $input);
        }

        $result = $validator->validate($input);

        if (!$result->isValid()) {
            $this->throwExceptionWithValidatorErrors($result->getMessages());
        }
    }

    /**
     * Maps the validator's errors to DomainException's error format.
     *
     * @param array $errors
     *
     * @throws InvalidRequestException
     */
    private function throwExceptionWithValidatorErrors(array $errors)
    {
        $compiledErrors = [];

        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $compiledErrors[] = [
                    'field' => $field,
                    'message' => $error,
                ];
            }
        }

        throw new InvalidRequestException($compiledErrors);
    }
}
