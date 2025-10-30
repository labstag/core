<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;

class HomeData extends DataAbstract implements DataInterface
{
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

    public function placeholder(): string
    {
        return '';
    }

    public function supportsAsset(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsData(object $entity): bool
    {
        return false;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }

    protected function getHome(): ?object
    {
        return $this->entityManager->getRepository(Page::class)->getOneByType(PageEnum::HOME->value);
    }
}
