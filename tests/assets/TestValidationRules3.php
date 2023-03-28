<?php

declare(strict_types=1);

namespace Linio\TestAssets;

use Linio\Common\Mezzio\Validation\ValidationRules;
use Particle\Validator\Validator;

class TestValidationRules3 implements ValidationRules
{
    /**
     * Builds the rules for particle/validator.
     */
    public function buildRules(Validator $validator, array $input)
    {
        $validator->required('key3')->equals($input['key4']);
    }
}
