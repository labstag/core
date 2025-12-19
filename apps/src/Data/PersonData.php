<?php

namespace Labstag\Data;

use Labstag\Entity\Person;
use Override;

class PersonData extends DataAbstract implements DataInterface
{

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Person;
    }
}