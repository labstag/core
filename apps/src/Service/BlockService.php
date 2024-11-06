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

    public function generate(array $blocks)
    {
        $tab = [];
        foreach ($blocks as $block) {
            $tab[] = [
                'templates' => $this->templates($block),
                'block'     => $block,
            ];
        }

        return $tab;
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

    public function getFields($block): array
    {
        if (!$block instanceof Block) {
            return [];
        }

        $type   = $block->getType();
        $fields = [];
        foreach ($this->blocks as $row) {
            if ($row->getType() == $type) {
                $fields = iterator_to_array($row->getFields($block));

                break;
            }
        }

        return $fields;
    }

    public function getNameByCode($code)
    {
        $name = '';
        foreach ($this->blocks as $block) {
            if ($block->getType() == $code) {
                $name = $block->getName();

                break;
            }
        }

        return $name;
    }

    public function getRegions(): array
    {
        return [
            'header' => 'header',
            'footer' => 'footer',
            'main'   => 'main',
        ];
    }

    // TODO : show content
    public function showContent(
        string $view,
        Block $block,
        array $data
    )
    {
        $content = null;
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $content = $row->content($view, $block, $data);

            break;
        }

        return $content;
    }

    private function templates(Block $block)
    {
        $template = null;
        foreach ($this->blocks as $row) {
            if ($block->getType() != $row->getType()) {
                continue;
            }

            $template = $row->templates();

            break;
        }

        return $template;
    }
}
