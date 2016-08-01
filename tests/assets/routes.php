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
    [
        'name' => 'test_valid_content_type',
        'path' => '/valid-content-type',
        'middleware' => [
            '\TestMiddleware',
        ],
        'allowed_methods' => ['GET'],
        'validation_rules' => [],
        'content_types' => ['supported'],
    ],
    [
        'name' => 'test_no_content_type',
        'path' => '/no-content-type',
        'middleware' => [
            '\TestMiddleware',
        ],
        'allowed_methods' => ['GET'],
        'validation_rules' => [],
        'content_types' => [null],
    ],
];
