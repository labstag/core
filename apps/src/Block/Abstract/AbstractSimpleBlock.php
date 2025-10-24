<?php

namespace Labstag\Block\Abstract;

use Labstag\Entity\Block;
use Override;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractSimpleBlock extends BlockLib
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
        unset($disable);

        $this->logger->debug(
            'Starting simple block generation',
            [
                'block_type' => $this->getType(),
                'block_id'   => $block->getId(),
            ]
        );

        if (!$this->validateBlockData($data)) {
            $this->setShow($block, false);

            return;
        }

        $blockData = $this->buildSimpleBlockData($block, $data);

        if ([] === $blockData) {
            $this->setShow($block, false);

            return;
        }

        $this->setData($block, $blockData);
    }

    /**
     * Build the data array for simple blocks.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    protected function buildSimpleBlockData(Block $block, array $data): array
    {
        return [
            'block' => $block,
            'data'  => $data,
        ];
    }

    /**
     * Validate block data before processing.
     *
     * @param mixed[] $data
     */
    protected function validateBlockData(array $data): bool
    {
        unset($data);

        // Override in specific blocks if needed
        return true;
    }
}
