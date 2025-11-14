<?php

namespace Labstag\Shortcode;

use Labstag\Entity\Page;
use Labstag\Repository\PageRepository;
use Labstag\Service\SlugService;

class PageUrlShortcode extends ShortcodeAbstract
{
    public function __construct(
        protected PageRepository $pageRepository,
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

        return '/' . $this->slugService->forEntity($entity);
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
