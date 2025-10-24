<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Repository\PageRepository;
use Labstag\Data\DataInterface;
use Labstag\Data\Abstract\DataLib;

class PageData extends DataLib implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private HomeData $homeData
    )
    {
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function generateSlug(object $entity): string
    {
        return $this->homeData->generateSlug($entity).$entity->getSlug();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Page;
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);
        if ($page instanceof Page) {
            return true;
        }

        return false;
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    private function getEntityBySlug(string $slug): ?object
    {
        return $this->pageRepository->findOneBy(['slug' => $slug]);
    }
}
