<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;

class ParagraphsBlock extends BlockLib
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
    public function getFields(Block $block, $pageName): iterable
    {
        unset($block, $pageName);

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

    #[Override]
    public function setData(Block $block, array $data)
    {
        $paragraphs = $block->getParagraphs()->getValues();
        if (0 == count($paragraphs)) {
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $this->paragraphService->generate($paragraphs, $data);
        $this->setHeader($block, $this->paragraphService->getContents($paragraphs, 'getHeader'));
        $this->setFooter($block, $this->paragraphService->getContents($paragraphs, 'getFooter'));

        parent::setData(
            $block,
            [
                'block'      => $block,
                'paragraphs' => $paragraphs,
            ]
        );
    }
}
