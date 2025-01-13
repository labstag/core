<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$configure = ECSConfig::configure();
$configure->withPaths(
    [
        __DIR__ . '/config',
        __DIR__ . '/features',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]
);

    // add a single rule
// $configure->withRules(
//     [
//         NoUnusedImportsFixer::class,
//     ]
// );

$configure->withPreparedSets(
    psr12: true,
    symplify: true,
    laravel: false,
    arrays: true,
    comments: true,
    docblocks: true,
    spaces: true,
    namespaces: true,
    controlStructures: true,
    phpunit: true,
    strict: false,
    cleanCode: true
);
return $configure;