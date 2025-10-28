<?php

namespace Labstag\Asset;

use Labstag\Entity\Story;

class StoryAsset extends AssetAbstract
{
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('story');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Story;
    }
}
