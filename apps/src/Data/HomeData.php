<?php

namespace Labstag\Data;

use Labstag\Data\Abstract\DataLib;
use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;

class HomeData extends DataLib implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository,
    )
    {
    }

    public function generateSlug(object $entity): string
    {
        unset($entity);
        $this->getHome();

        return '';
    }

    public function getEntity(string $slug): object
    {
        unset($slug);
        return $this->getHome();
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function match(string $slug): bool
    {
        return '' === $slug || is_null($slug);
    }

    public function supports(object $entity): bool
    {
        return false;
    }

    private function getHome(): ?object
    {
        return $this->pageRepository->getOneByType(PageEnum::HOME->value);
    }
}
