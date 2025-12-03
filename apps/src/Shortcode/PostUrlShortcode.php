<?php

namespace Labstag\Shortcode;

use Labstag\Entity\Post;
use Labstag\Repository\PostRepository;
use Labstag\Service\SlugService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostUrlShortcode extends ShortcodeAbstract
{
    public function __construct(
        protected PostRepository $postRepository,
        protected UrlGeneratorInterface $urlGenerator,
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

        $params = $this->slugService->forEntity($entity);

        return $this->urlGenerator->generate('front', $params);
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
