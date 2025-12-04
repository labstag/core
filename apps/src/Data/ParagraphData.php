<?php

namespace Labstag\Data;

use Labstag\Entity\Paragraph;
use Override;

class ParagraphData extends DataAbstract implements DataInterface
{
    #[Override]
    public function placeholder(): string
    {
        return $this->configPlaceholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Paragraph;
    }
}
