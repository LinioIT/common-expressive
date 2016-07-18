<?php

declare(strict_types=1);

return [
    [
        'name' => 'test',
        'path' => '/',
        'middleware' => [
            '\TestMiddleware',
        ],
        'allowed_methods' => ['GET'],
        'validation_rules' => [\Linio\TestAssets\TestValidationRules::class],
    ],
];
