<?php

namespace Labstag\Data;

use Labstag\Data\Abstract\DataLib;
use Labstag\Entity\Chapter;
use Labstag\Repository\ChapterRepository;

class ChapterData extends DataLib implements DataInterface
{
    public function __construct(
        private StoryData $storyData,
        private ChapterRepository $chapterRepository,
    )
    {
    }

    public function generateSlug(object $entity): string
    {
        return $this->storyData->generateSlug($entity->getRefstory()) . '/' . $entity->getSlug();
    }

    public function getEntity(string $slug): object
    {
        return $this->getEntityBySlug($slug);
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);

        return $page instanceof Chapter;
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Chapter;
    }

    private function getEntityBySlug(string $slug): ?object
    {
        if (0 === substr_count($slug, '/')) {
            return null;
        }

        $slugSecond = basename($slug);
        $slugFirst  = dirname($slug);

        if (false === $this->storyData->match($slugFirst)) {
            return null;
        }

        $story      = $this->storyData->getEntity($slugFirst);

        return $this->chapterRepository->findOneBy(
            [
                'refstory' => $story,
                'slug'     => $slugSecond,
            ]
        );
    }
}
