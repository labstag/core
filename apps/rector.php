<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

$configure = RectorConfig::configure();
$configure->withPaths([
    __DIR__ . '/config',
    __DIR__ . '/features',
    __DIR__ . '/public',
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);
// uncomment to reach your current PHP version
// ->withPhpSets()
$configure->withTypeCoverageLevel(0);
$configure->withDeadCodeLevel(0);
$configure->withCodeQualityLevel(0);
return $configure;