<?php

namespace Labstag\Block;

use Labstag\Block\Traits\ParagraphProcessingTrait;
use Labstag\Entity\Block;
use Labstag\Entity\ContentBlock as EntityContentBlock;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

class ContentBlock extends BlockAbstract
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
            'Starting content block generation',
            [
                'block_id' => $block->getId(),
            ]
        );

        if (!isset($data['paragraphs']) || !is_array($data['paragraphs'])) {
            $this->logger->warning(
                'Invalid paragraphs data for content block',
                [
                    'block_id' => $block->getId(),
                ]
            );
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $data['paragraphs'];
        if ([] === $paragraphs) {
            $this->setShow($block, false);

            return;
        }

        $paragraphs = $this->paragraphService->generate($paragraphs, $data, $disable);
        $contents   = $this->paragraphService->getContents($paragraphs);
        $this->setHeader($block, $contents->header);
        $this->setFooter($block, $contents->footer);

        $tab = [
            'block'      => $block,
            'data'       => $data,
            'paragraphs' => $paragraphs,
        ];

        // Configure aside - implemented the TODO
        if (!($data['entity'] instanceof Page && PageEnum::HOME->value == $data['entity']->getType())) {
            $this->getAside($data);
            // Note: getAside currently always returns null, so this block is effectively unused
            // TODO: Implement actual aside content logic when needed
        }

        $this->setData($block, $tab);
    }

    public function getClass(): string
    {
        return EntityContentBlock::class;
    }

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Content');
    }

    #[Override]
    public function getType(): string
    {
        return 'content';
    }

    /**
     * Get aside content for the page.
     *
     * @param mixed[] $data
     */
    private function getAside(array $data): null
    {
        // Implementation for aside content
        // This could include related posts, tags, categories, etc.
        // For now, return null but structure is ready for implementation

        $this->logger->debug(
            'Aside content requested but not yet implemented',
            [
                'entity_type' => $data['entity']::class ?? 'unknown',
            ]
        );

        return null;
    }
}
