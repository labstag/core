<?php

namespace Labstag\Data;

use Override;
use Labstag\Entity\Edito;

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

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Edito;
    }
}
