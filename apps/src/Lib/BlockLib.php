<?php

namespace Labstag\Lib;

use Labstag\Entity\Block;

class BlockLib
{
    public function getFields(Block $block): iterable
    {
        unset($block);

        return [];
    }

    public function getName(): string
    {
        return '';
    }

    public function getType(): string
    {
        return '';
    }

    public function useIn(): array
    {
        return [];
    }
}
