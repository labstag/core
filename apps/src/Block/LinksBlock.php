<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class LinksBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block)
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render(
            $view,
            $this->getData($block)
        );
    }

    #[Override]
    public function generate(Block $block, array $data)
    {
        $this->setData(
            $block,
            [
                'block' => $block,
                'data'  => $data,
            ]
        );
    }

    #[Override]
    public function getFields(Block $block, $pageName): iterable
    {
        unset($block, $pageName);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Links';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'links';
    }
}
