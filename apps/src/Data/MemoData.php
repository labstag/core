<?php

namespace Labstag\Data;

use Labstag\Entity\Memo;
use Override;

class MemoData extends DataAbstract implements DataInterface
{
    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('memo');
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
        return $entity instanceof Memo;
    }
}
