<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('somedir')
    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();


return $config->setRules([
    '@PSR12' => true,
    '@PhpCsFixer' => true,
    'no_extra_blank_lines' => false,
    'multiline_whitespace_before_semicolons' => false,
    'no_whitespace_before_comma_in_array' => true,
    'trailing_comma_in_multiline' => true,
    'braces' => true,
    'array_indentation' => true,
    'array_syntax' => ['syntax' => 'short'],
    'class_definition' => ['space_before_parenthesis' => true],
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'multiline_comment_opening_closing' => false,
    'no_unused_imports' => false,
    'phpdoc_to_comment' => false,
    'yoda_style' => false,
    'increment_style' => ['style' => 'post'],
    'cast_spaces' => ['space' => 'none'],
    'concat_space' => [
        'spacing' => 'one',
    ],
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => null,
        'import_functions' => null,
    ],
])->setFinder($finder);
