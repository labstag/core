<?php

namespace Labstag\Block\Traits;

use Labstag\Entity\Block;

trait ParagraphProcessingTrait
{
    /**
     * Process paragraphs for blocks that handle paragraph collections.
     *
     * @param mixed[] $data
     *
     * @return mixed[]|null Returns processed paragraphs or null if no paragraphs found
     */
    protected function processParagraphs(Block $block, array $data, bool $disable): ?array
    {
        $paragraphs = $block->getParagraphs()->getValues();
        if (0 === count($paragraphs)) {
            $this->logger->debug(
                'No paragraphs found for block',
                [
                    'block_type' => $this->getType(),
                    'block_id'   => $block->getId(),
                ]
            );

            return null;
        }

        $paragraphs = $this->paragraphService->generate($paragraphs, $data, $disable);
        $contents   = $this->paragraphService->getContents($paragraphs);

        $this->setHeader($block, $contents->header);
        $this->setFooter($block, $contents->footer);

        $this->logger->debug(
            'Paragraphs processed successfully',
            [
                'block_type'      => $this->getType(),
                'block_id'        => $block->getId(),
                'paragraph_count' => count($paragraphs),
            ]
        );

        return $paragraphs;
    }

    /**
     * Validate that required data exists for paragraph processing.
     *
     * @param mixed[] $data
     */
    protected function validateParagraphData(array $data): bool
    {
        unset($data);

        // Override in specific blocks if needed
        return true;
    }
}
