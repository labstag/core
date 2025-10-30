<?php

namespace Labstag\Block;

use Labstag\Block\Traits\ParagraphProcessingTrait;
use Labstag\Entity\Block;
use Override;
use Symfony\Component\HttpFoundation\Response;

abstract class ParagraphBlockAbstract extends BlockAbstract
{
    use ParagraphProcessingTrait;

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
        $this->logger->debug(
            'Starting paragraph block generation',
            [
                'block_type' => $this->getType(),
                'block_id'   => $block->getId(),
            ]
        );

        if (!$this->validateParagraphData($data)) {
            $this->setShow($block, false);

            return;
        }

        // Check for additional conditions specific to block type
        if ($this->shouldHideBlock($block, $data)) {
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $this->processParagraphs($block, $data, $disable);
        if (is_null($paragraphs)) {
            $this->setShow($block, false);

            return;
        }

        $this->setData($block, $this->buildBlockData($block, $paragraphs, $data));
    }

    /**
     * Build the data array for the block.
     *
     * @param mixed[] $paragraphs
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    protected function buildBlockData(Block $block, array $paragraphs, array $data): array
    {
        unset($data);

        return [
            'block'      => $block,
            'paragraphs' => $paragraphs,
        ];
    }

    /**
     * Check if block should be hidden based on specific conditions.
     *
     * @param mixed[] $data
     */
    protected function shouldHideBlock(Block $block, array $data): bool
    {
        unset($block, $data);

        // Override in specific blocks if needed
        return false;
    }
}
