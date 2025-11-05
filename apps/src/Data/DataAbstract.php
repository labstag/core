<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag('labstag.datas')]
abstract class DataAbstract
{
    public function __construct(
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
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

    protected function getHome(): ?object
    {
        return $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::HOME->value,
            ]
        );
    }

    protected function globalPlaceholder(string $key): string
    {
        return $this->fileService->asset($this->configurationService->getConfiguration(), $key . 'Placeholder');
    }
}
