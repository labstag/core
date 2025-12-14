<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\CodeQuality\Rector\Ternary\TernaryEmptyArrayArrayDimFetchToCoalesceRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\TryCatch\RemoveDeadCatchRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;
use Rector\ValueObject\PhpVersion;

$configure = RectorConfig::configure();
$configure->withPaths(
    [
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]
);
$configure->withPhpSets(php84: true);
$configure->withAttributesSets(
    symfony: true,
);
// Importer les classes au lieu d'utiliser les FQCN
$configure->withImportNames(importShortClasses: true, removeUnusedImports: true);
$configure->withPreparedSets(
    deadCode: true,
    codeQuality: true,
    codingStyle: true,
    typeDeclarations: true,
    privatization: true,
    naming: true,
    instanceOf: true,
    earlyReturn: true,
    strictBooleans: false,
    carbon: false,
    rectorPreset: false,
    phpunitCodeQuality: true,
    doctrineCodeQuality: true,
    symfonyCodeQuality: true,
    symfonyConfigs: true
);
// Optionnel : Améliore la précision pour les règles Symfony (si vous avez un conteneur compilé)
// $configure->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');

// Règles individuelles supplémentaires (non incluses dans les sets)
$configure->withRules([
    TypedPropertyFromAssignsRector::class,              // Ajoute les types aux propriétés basés sur les assignations
]);

$configure->withSkip(
    [
        ReadOnlyPropertyRector::class,
        AddTypeToConstRector::class,
        ReadOnlyClassRector::class,
    ]
);
return $configure;