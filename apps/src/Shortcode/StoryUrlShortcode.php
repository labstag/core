<?php

namespace Labstag\Shortcode;

use Labstag\Entity\Story;
use Labstag\Repository\StoryRepository;
use Labstag\Service\SlugService;

class StoryUrlShortcode extends ShortcodeAbstract
{
    public function __construct(
        protected StoryRepository $storyRepository,
        protected SlugService $slugService,
    )
    {
    }

    public function content(array $matches): ?string
    {
        $entity = $this->storyRepository->find($matches[1]);
        if (!$entity instanceof Story) {
            return null;
        }

        if (!$entity->isEnable()) {
            return null;
        }

        return '/' . $this->slugService->forEntity($entity);
    }

    public function generate(string $id): string
    {
        return sprintf('[%s:%s]', 'storyurl', $id);
    }

    public function getPattern(): string
    {
        return '/\[(\w+)(.*?)\]/';
    }
}
