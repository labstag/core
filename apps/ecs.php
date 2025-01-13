<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
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
    common: false,
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
$configure->withSkip(
    [
        NotOperatorWithSuccessorSpaceFixer::class,
        IsNullFixer::class,
        BracesPositionFixer::class,
        GlobalNamespaceImportFixer::class,
    ]
);
$configure->withPhpCsFixerSets(
    symfony: true
);
return $configure;