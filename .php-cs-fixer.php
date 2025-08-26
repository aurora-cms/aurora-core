<?php

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$header = <<<HDR
This file is part of Aurora Core.

(c) The Aurora Core contributors
License: MIT
HDR;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->exclude('var')
    ->ignoreVCSIgnored(true)
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'header_comment' => ['header' => $header, 'separate' => 'both'],
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => ['scope' => 'namespaced', 'include' => ['@compiler_optimized']],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_to_comment' => false,
        'combine_consecutive_unsets' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    ])
    ->setFinder($finder)
;
