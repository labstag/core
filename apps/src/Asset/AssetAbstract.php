<?php

namespace Labstag\Asset;

use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('labstag.assets')]
abstract class AssetAbstract
{
    public function __construct(
        private FileService $fileService,
        private ConfigurationService $configurationService,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        return $this->fileService->asset($entity, $field);
    }

    public function configPlaceholder(): string
    {
        return $this->fileService->asset($this->configurationService->getConfiguration(), 'placeholder');
    }

    protected function globalPlaceholder(string $key): string
    {
        return $this->fileService->asset($this->configurationService->getConfiguration(), $key . 'Placeholder');
    }
}
