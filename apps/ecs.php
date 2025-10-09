<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\LanguageConstruct\IsNullFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$configure = ECSConfig::configure();

$configure->withPaths([
    __DIR__ . '/config',
    __DIR__ . '/features',
    __DIR__ . '/public',
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);

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

$configure->withSkip([
    NotOperatorWithSuccessorSpaceFixer::class,
    IsNullFixer::class,
    BracesPositionFixer::class,
    GlobalNamespaceImportFixer::class,
]);

$configure->withPhpCsFixerSets(
    symfony: true
);

$configure->withConfiguredRule(
    YodaStyleFixer::class,
    [
        'equal' => true,
        'identical' => true,
        'less_and_greater' => true,
    ]
);

$configure->withConfiguredRule(
    BinaryOperatorSpacesFixer::class,
    [
        'default' => 'align',
        'operators' => [
            '='  => 'align',
            '=>' => 'align',
        ],
    ]
);

$configure->withConfiguredRule(
    OrderedClassElementsFixer::class,
    [
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'method_public',
            'method_protected',
            'method_private',
        ],
        'sort_algorithm' => 'alpha',
    ]
);

return $configure;
