<?php

namespace Labstag\Data;

use Override;
use Labstag\Entity\Memo;

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

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Memo;
    }
}
