<?php

namespace Labstag\Data;

use Labstag\Enum\PageEnum;
use Labstag\Repository\PageRepository;
use Labstag\Data\DataInterface;
use Labstag\Data\Abstract\DataLib;

class HomeData extends DataLib implements DataInterface
{
    public function __construct(
        private PageRepository $pageRepository
    )
    {
    }

    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    public function generateSlug(object $entity): string
    {
        $home = $this->getHome();

        return '';
    }

    public function supports(object $entity): bool
    {
        return false;
    }

    public function match(string $slug): bool
    {
        if ('' === $slug || is_null($slug)) {
            return true;
        }

        return false;
    }

    private function getHome(): ?object
    {
        return $this->pageRepository->getOneByType(PageEnum::HOME->value);
    }

    public function getEntity(string $slug): object
    {
        return $this->getHome();
    }
}
