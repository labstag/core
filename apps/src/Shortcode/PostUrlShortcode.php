<?php

namespace Labstag\Shortcode;

use Labstag\Entity\Post;
use Labstag\Repository\PostRepository;
use Labstag\Service\SlugService;

class PostUrlShortcode extends ShortcodeAbstract
{
    public function __construct(
        protected PostRepository $postRepository,
        protected SlugService $slugService,
    )
    {
    }

    public function content(string $id): ?string
    {
        $entity = $this->postRepository->find($id);
        if (!$entity instanceof Post) {
            return null;
        }

        if (!$entity->isEnable()) {
            return null;
        }

        return '/' . $this->slugService->forEntity($entity);
    }

    public function generate(string $id): string
    {
        return sprintf('[%s:%s]', 'posturl', $id);
    }

    public function getPattern(): string
    {
        return '/\[posturl:(.*?)]/';
    }
}
