<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Field\WysiwygField;
use Labstag\Lib\BlockLib;
use Override;

class HtmlBlock extends BlockLib
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
        $wysiwygField = WysiwygField::new('content', 'Texte');

        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'HTML';
    }

    #[Override

    ]
    public function getType(): string
    {
        return 'html';
    }
}
