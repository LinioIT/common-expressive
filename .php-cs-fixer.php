<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@Symfony' => true,
            'array_syntax' => ['syntax' => 'short'],
            'blank_line_after_opening_tag' => true,
            'concat_space' => ['spacing' => 'one'],
            'declare_strict_types' => true,
            'list_syntax' => ['syntax' => 'short'],
            'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
            'modernize_types_casting' => true,
            'multiline_whitespace_before_semicolons' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ordered_imports' => true,
            'phpdoc_align' => false,
            'phpdoc_order' => true,
            'php_unit_construct' => true,
            'php_unit_dedicate_assert' => true,
            'single_line_comment_style' => true,
            'ternary_to_null_coalescing' => true,
        ]
    )
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setRiskyAllowed(true);

return $config;
