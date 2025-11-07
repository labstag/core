<?php

namespace Labstag\Data;

use Labstag\Entity\User;

class UserData extends DataAbstract implements DataInterface
{
    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('user');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof User;
    }
}
