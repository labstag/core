<?php

namespace Labstag\Service;

use Labstag\Entity\Block;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class BlockService
{
    public function __construct(
        #[AutowireIterator('labstag.blocks')]
        private readonly iterable $blocks
    )
    {
    }

    public function getAll($entity): array
    {
        $blocks = [];
        foreach ($this->blocks as $block) {
            $inUse = $block->useIn();
            $type  = $block->getType();
            $name  = $block->getName();
            if ((in_array($entity, $inUse) && $block->isEnable()) || is_null($entity)) {
                $blocks[$name] = $type;
            }
        }

        return $blocks;
    }

    public function getFieldsCrudEA($block)
    {
        if (!$block instanceof Block) {
            return [];
        }

        $type   = $block->getType();
        $fields = [];
        foreach ($this->blocks as $row) {
            if ($row->getType() == $type) {
                $fields = $row->getFields($block);

                break;
            }
        }

        return $fields;
    }

    public function getRegions(): array
    {
        return [
            'header' => 'header',
            'footer' => 'footer',
            'main'   => 'main',
        ];
    }
}
