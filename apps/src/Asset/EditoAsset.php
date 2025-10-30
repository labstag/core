<?php

namespace Labstag\Asset;

use Labstag\Entity\Edito;

class EditoAsset extends AssetAbstract
{
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('edito');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Edito;
    }
}
