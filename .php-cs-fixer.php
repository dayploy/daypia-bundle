<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['src'])
;

$config = (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
;

return $config->setRules([
    '@Symfony' => true,
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_superfluous_phpdoc_tags' => false,
    'phpdoc_to_comment' => false,
    'phpdoc_var_without_name' => false,
    'single_line_throw' => false,
])
    ->setFinder($finder)
;
