<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class ContentBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block, array $data)
    {
        $paragraphs = $data['paragraphs'];
        if (0 == count($paragraphs)) {
            return null;
        }

        $paragraphs = $this->paragraphService->generate($paragraphs);

        return $this->render(
            $view,
            [
                'block'      => $block,
                'data'       => $data,
                'paragraphs' => $paragraphs,
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
        return 'Content';
    }

    #[Override]
    public function getType(): string
    {
        return 'content';
    }
}
