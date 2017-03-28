<?php

declare(strict_types=1);

namespace Linio\TestAssets;

use Linio\Common\Expressive\Validation\ValidationRules;
use Particle\Validator\Validator;

class TestValidationRules2 implements ValidationRules
{
    public function buildRules(Validator $validator, array $input): void
    {
        $validator->required('key2');
    }
}
