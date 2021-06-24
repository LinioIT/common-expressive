<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Validation;

use Particle\Validator\Validator;

interface ValidationRules
{
    /**
     * Builds the rules for particle/validator.
     */
    public function buildRules(Validator $validator, array $input);
}
