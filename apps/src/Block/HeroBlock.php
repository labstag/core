<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Entity\HeroBlock as EntityHeroBlock;
use Override;

class HeroBlock extends ParagraphBlockAbstract
{
    public function getClass(): string
    {
        return EntityHeroBlock::class;
    }

    #[Override]
    public function getName(): string
    {
        return 'Hero';
    }

    #[Override]
    public function getType(): string
    {
        return 'hero';
    }

    /**
     * @param mixed[] $data
     */
    #[Override]
    protected function shouldHideBlock(Block $block, array $data): bool
    {
        unset($block);

        return $this->siteService->isHome($data);
    }
}
