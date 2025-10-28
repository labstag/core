<?php

namespace Labstag\Asset;

use Labstag\Entity\Paragraph;

class ParagraphAsset extends AssetAbstract
{
    public function placeholder(): string
    {

        return $this->configPlaceholder();
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof Paragraph;
    }
}
