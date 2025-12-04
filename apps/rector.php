<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FunctionLike\FunctionLikeToFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;

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
$configure->withSkip(
    [
        ReadOnlyPropertyRector::class,
        AddTypeToConstRector::class,
        ReadOnlyClassRector::class,
    ]
);
return $configure;