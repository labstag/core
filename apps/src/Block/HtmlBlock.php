<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class HtmlBlock extends BlockLib
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
        return 'HTML';
    }

    #[Override]
    public function getType(): string
    {
        return 'html';
    }
}
