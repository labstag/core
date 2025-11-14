<?php

namespace Labstag\Data;

class HomeData extends DataAbstract implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): string
    {
        unset($entity);

        return '';
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        unset($slug);

        return $this->getHome();
    }

    #[\Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[\Override]
    public function match(?string $slug): bool
    {
        return '' === $slug || is_null($slug);
    }
}
