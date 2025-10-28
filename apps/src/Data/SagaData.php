<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Entity\Saga;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Repository\SagaRepository;

class SagaData extends DataAbstract implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private SagaRepository $sagaRepository,
        private PageData $pageData,
    )
    {
    }

    public function generateSlug(object $entity): string
    {
        $page = $this->pageRepository->findOneBy(
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

    public function supports(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    private function getEntityBySlug(string $slug): ?object
    {
        if (0 === substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst  = dirname($slug);

        $page = $this->pageRepository->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::MOVIES->value) {
            return null;
        }

        return $this->sagaRepository->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
