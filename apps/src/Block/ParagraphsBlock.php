<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class ParagraphsBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block, array $data)
    {
        $paragraphs = $block->getParagraphs();
        if (0 == count($paragraphs)) {
            return null;
        }

        $paragraphs = $this->paragraphService->generate($paragraphs);

        return $this->render(
            $view,
            [
                'block'      => $block,
                'paragraphs' => $paragraphs,
            ]
        );
    }

    #[Override]
    public function getFields(Block $block): iterable
    {
        unset($block);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Paragraphs';
    }

    #[Override]
    public function getType(): string
    {
        return 'paragraphs';
    }
}
