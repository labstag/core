<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;

$configure = RectorConfig::configure();
$configure->withPaths([
    __DIR__ . '/config',
    __DIR__ . '/features',
    __DIR__ . '/public',
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);
// uncomment to reach your current PHP version
// $configure->withPhpSets()
$configure->withPhpSets(php84: true);
$configure->withAttributesSets();
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
    carbon: true,
    rectorPreset: false,
    phpunitCodeQuality: true,
    doctrineCodeQuality: true,
    symfonyCodeQuality: true,
    symfonyConfigs: true
);
return $configure;