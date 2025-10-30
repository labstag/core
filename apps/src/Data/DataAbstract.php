<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('labstag.datas')]
abstract class DataAbstract
{
    public function __construct(
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        return $this->fileService->asset($entity, $field);
    }

    public function getShortcodes(): array
    {
        return [];
    }

    protected function configPlaceholder(): string
    {
        return $this->fileService->asset($this->configurationService->getConfiguration(), 'placeholder');
    }

    protected function globalPlaceholder(string $key): string
    {
        return $this->fileService->asset($this->configurationService->getConfiguration(), $key . 'Placeholder');
    }
}
