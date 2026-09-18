<?php

namespace Labstag\Shortcode;

use Labstag\Entity\Page;
use Labstag\Repository\PageRepository;
use Labstag\Service\SlugService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageUrlShortcode extends ShortcodeAbstract
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected UrlGeneratorInterface $urlGenerator,
        protected SlugService $slugService,
    )
    {
    }

    public function content(string $id): ?string
    {
        $entity = $this->pageRepository->find($id);
        if (!$entity instanceof Page) {
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
        return sprintf('[%s:%s]', 'pageurl', $id);
    }

    public function getPattern(): string
    {
        return '/\[pageurl:(.*?)]/';
    }
}
