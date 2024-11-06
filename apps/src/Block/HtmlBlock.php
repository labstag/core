<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Field\WysiwygField;
use Labstag\Lib\BlockLib;
use Override;

class HtmlBlock extends BlockLib
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
    public function getFields(Block $block): iterable
    {
        $wysiwygField = WysiwygField::new('content', 'Texte');

        yield $wysiwygField;
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
