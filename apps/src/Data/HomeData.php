<?php

namespace Labstag\Data;

use Override;

class HomeData extends DataAbstract implements DataInterface
{
    #[Override]
    public function generateSlug(object $entity): array
    {
        unset($entity);

        return ['slug' => ''];
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        unset($slug);

        return $this->getHome();
    }

    #[Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[Override]
    public function match(?string $slug): bool
    {
        return '' === $slug || is_null($slug);
    }
}
