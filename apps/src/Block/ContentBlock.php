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

    #[Override]
    public function setData(Block $block, array $data)
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

        $this->setHeader($block, $this->paragraphService->getContents($paragraphs, 'getHeader'));
        $this->setFooter($block, $this->paragraphService->getContents($paragraphs, 'getFooter'));

        parent::setData(
            $block,
            [
                'block'      => $block,
                'data'       => $data,
                'paragraphs' => $paragraphs,
            ]
        );
    }
}
