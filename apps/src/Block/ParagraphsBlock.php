<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Lib\BlockLib;
use Override;
use Symfony\Component\HttpFoundation\Response;

class ParagraphsBlock extends BlockLib
{
    #[Override]
    public function content(string $view, Block $block): ?Response
    {
        if (!$this->isShow($block)) {
            return null;
        }

        return $this->render($view, $this->getData($block));
    }

    /**
     * @param mixed[] $data
     */
    #[Override]
    public function generate(Block $block, array $data, bool $disable): void
    {
        $paragraphs = $block->getParagraphs()->getValues();
        if (0 == count($paragraphs)) {
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $this->paragraphService->generate($paragraphs, $data, $disable);
        $contents   = $this->paragraphService->getContents($paragraphs);
        $this->setHeader($block, $contents->header);
        $this->setFooter($block, $contents->footer);

        $this->setData(
            $block,
            [
                'block'      => $block,
                'paragraphs' => $paragraphs,
            ]
        );
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
