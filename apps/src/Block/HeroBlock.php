<?php

namespace Labstag\Block;

use Labstag\Block\Abstract\AbstractParagraphBlock;
use Labstag\Entity\Block;
use Override;

class HeroBlock extends AbstractParagraphBlock
{
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
        return $this->siteService->isHome($data);
    }
}
