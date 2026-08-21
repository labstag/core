<?php

namespace Labstag\Shortcode;

use Labstag\Entity\Story;
use Labstag\Repository\StoryRepository;
use Labstag\Service\SlugService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StoryUrlShortcode extends ShortcodeAbstract
{
    public function __construct(
        protected StoryRepository $storyRepository,
        protected UrlGeneratorInterface $urlGenerator,
        protected SlugService $slugService,
    )
    {
    }

    public function content(string $id): ?string
    {
        $entity = $this->storyRepository->find($id);
        if (!$entity instanceof Story) {
            return null;
        }

        if (!$entity->isEnable()) {
            return null;
        }

        $params = $this->slugService->forEntity($entity);

        return $this->urlGenerator->generate('front', $params);
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
