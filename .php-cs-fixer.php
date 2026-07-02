<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$pint = json_decode((string) file_get_contents(__DIR__ . '/pint.json'), true);

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/public',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->exclude(...$pint['exclude'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        ...$pint['rules'],
    ])
    ->setParallelConfig(
        ParallelConfigFactory::detect()
    )
    ->setRiskyAllowed(true)
;
