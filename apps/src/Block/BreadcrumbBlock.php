<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class BreadcrumbBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block)
    {
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
        return 'Breadcrumb';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'breadcrumb';
    }
}
