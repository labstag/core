<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class BreadcrumbBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block, array $data)
    {
        return $this->render(
            $view,
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

    #[Override]
    public function getType(): string
    {
        return 'breadcrumb';
    }
}
