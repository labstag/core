<?php

namespace Labstag\Data;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Page;
use Labstag\Entity\Saga;
use Labstag\Enum\PageEnum;
use Labstag\Service\ConfigurationService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class SagaData extends DataAbstract implements DataInterface
{
    public function __construct(
        protected PageData $pageData,
        protected FileService $fileService,
        protected ConfigurationService $configurationService,
        protected EntityManagerInterface $entityManager,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    )
    {
        parent::__construct($fileService, $configurationService, $entityManager, $requestStack, $translator);
    }

    public function generateSlug(object $entity): string
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::MOVIES->value,
            ]
        );

        return $this->pageData->generateSlug($page) . '/' . $entity->getSlug();
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Saga;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('saga');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    public function supportsData(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }

    protected function getEntityBySlug(string $slug): ?object
    {
        if (0 === substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst  = dirname($slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::MOVIES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Saga::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
