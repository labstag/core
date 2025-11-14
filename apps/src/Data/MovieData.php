<?php

namespace Labstag\Data;

use Labstag\Entity\Movie;

class MovieData extends SagaData implements DataInterface
{
    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('movie');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Movie;
    }
}
