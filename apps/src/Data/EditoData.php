<?php

namespace Labstag\Data;

use Labstag\Entity\Edito;
use Override;

class EditoData extends DataAbstract implements DataInterface
{
    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('edito');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    public function getDefaultImage(object $entity): ?string
    {
        return $entity->getImg();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Edito;
    }
}
