<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class ContentBlock extends BlockLib
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
        $paragraphs = $data['paragraphs'];
        if (0 == count($paragraphs)) {
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $this->paragraphService->generate(
            $paragraphs,
            $data
        );

        $contents = $this->paragraphService->getContents($paragraphs);
        $this->setHeader($block, $contents->header);
        $this->setFooter($block, $contents->footer);

        $this->setData(
            $block,
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
