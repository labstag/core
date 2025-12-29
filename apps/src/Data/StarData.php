<?php

namespace Labstag\Data;

use Labstag\Entity\Star;
use Override;

class StarData extends DataAbstract implements DataInterface
{
    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('star');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function getDefaultImage(object $entity): string
    {
        return $entity->getImg();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Star;
    }
}
