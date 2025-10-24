<?php

namespace Labstag\Data;

use Labstag\Entity\Chapter;
use Labstag\Repository\ChapterRepository;
use Labstag\Data\DataInterface;
use Labstag\Data\Abstract\DataLib;

class ChapterData extends DataLib implements DataInterface
{
    public function __construct(
        private StoryData $storyData,
        private ChapterRepository $chapterRepository
    )
    {

    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function generateSlug(object $entity): string
    {
        return $this->storyData->generateSlug($entity->getRefstory()).'/'.$entity->getSlug();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Chapter;
    }
    
    public function match(string $slug): bool
    {
        $page = $this->getEntityBySlug($slug);
        if ($page instanceof Chapter) {
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

        if ($this->storyData->match($slugFirst) == false) {
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
