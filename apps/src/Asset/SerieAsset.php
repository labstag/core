<?php

namespace Labstag\Asset;

use Labstag\Entity\Serie;

class SerieAsset extends AssetAbstract
{
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('serie');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Serie;
    }
}
