<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Repository\PageRepository;

class PageData extends DataAbstract implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private HomeData $homeData,
    )
    {
    }

    public function generateSlug(object $entity): string
    {
        return $this->homeData->generateSlug($entity) . $entity->getSlug();
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

        return $page instanceof Page;
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Page;
    }

    private function getEntityBySlug(string $slug): ?object
    {
        return $this->pageRepository->findOneBy(
            ['slug' => $slug]
        );
    }
}
