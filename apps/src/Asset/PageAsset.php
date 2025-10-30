<?php

namespace Labstag\Asset;

use Labstag\Entity\Page;

class PageAsset extends AssetAbstract
{
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('page');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Page;
    }
}
