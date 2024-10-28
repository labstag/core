<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class LinksBlock extends BlockLib
{
    #[Override]
    public function getFields(Block $block): iterable
    {
        unset($block);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Links';
    }

    #[Override]
    public function getType(): string
    {
        return 'links';
    }
}
