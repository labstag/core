<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Labstag\Service\SlugService;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
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
        protected Security $security,
        protected RouterInterface $router,
        protected SlugService $slugService,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        return $this->fileService->asset($entity, $field);
    }

    public function generateSlug(object $entity): string
    {
        unset($entity);

        return '';
    }

    public function getEntity(?string $slug): object
    {
        unset($slug);

        return new stdClass();
    }

    public function getShortcodes(): array
    {
        return [];
    }

    public function getTitle(object $entity): string
    {
        unset($entity);

        return '';
    }

    public function match(?string $slug): bool
    {
        unset($slug);

        return false;
    }

    public function placeholder(): string
    {
        return '';
    }

    public function scriptBefore(object $entity, Response $response): Response
    {
        unset($entity);

        return $response;
    }

    public function supportsAsset(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsData(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsJsonLd(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsScriptBefore(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsShortcode(string $className): bool
    {
        unset($className);

        return false;
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
