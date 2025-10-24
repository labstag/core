<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Entity\Serie;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Repository\SerieRepository;
use Labstag\Data\DataInterface;
use Labstag\Data\Abstract\DataLib;

class SerieData extends DataLib implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository,
        private SerieRepository $serieRepository,
        private PageData $pageData,
    )
    {
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function generateSlug(object $entity): string
    {
        $page = $this->pageRepository->findOneBy(
            [
                'type' => PageEnum::SERIES->value,
            ]
        );
        return $this->pageData->generateSlug($page).'/'.$entity->getSlug();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Serie;
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);
        if ($page instanceof Serie) {
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
        if (0 == substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst = dirname($slug);

        $page = $this->pageRepository->findOneBy(['slug' => $slugFirst]);
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::SERIES->value) {
            return null;
        }

        return $this->serieRepository->findOneBy(
            [
                'slug' => $slugSecond,
            ]
        );
    }
}
