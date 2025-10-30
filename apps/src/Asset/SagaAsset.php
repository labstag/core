<?php

namespace Labstag\Asset;

use Labstag\Entity\Saga;

class SagaAsset extends AssetAbstract
{
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('saga');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Saga;
    }
}
