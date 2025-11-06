<?php

namespace Labstag\Data;

use Labstag\Entity\Movie;

class MovieData extends DataAbstract implements DataInterface
{
    public function generateSlug(object $entity): string
    {
        unset($entity);

        return '';
    }

    public function getEntity(?string $slug): object
    {
        unset($slug);

        return new Movie();
    }

    public function getTitle(object $entity): string
    {
        unset($entity);

        return '';
    }

    public function match(?string $slug): bool
    {
        unset($slug);

        return false;
    }

    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('movie');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Movie;
    }

    public function supportsData(object $entity): bool
    {
        unset($entity);

        return false;
    }

    public function supportsShortcode(string $className): bool
    {
        return false;
    }
}
