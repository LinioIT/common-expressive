<?php

declare(strict_types=1);

namespace Linio\TestAssets;

use Linio\Common\Mezzio\Validation\ValidationRules;
use Particle\Validator\Validator;

class TestValidationRules implements ValidationRules
{
    /**
     * Builds the rules for particle/validator.
     */
    public function buildRules(Validator $validator, array $input)
    {
        $validator->required('key');
    }
}
