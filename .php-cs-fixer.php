<?php

use PhpCsFixer\Config;

$finder = Symfony\Component\Finder\Finder::create()
    ->notPath('vendor')
    ->in([
        __DIR__ . '/src',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);
$config = new Config();

return $config
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
    ])
    ->setFinder($finder);
