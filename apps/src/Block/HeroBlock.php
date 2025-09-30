<?php

namespace Labstag\Block;

use Labstag\Entity\Block;
use Labstag\Block\Abstract\AbstractParagraphBlock;
use Override;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;


class HeroBlock extends AbstractParagraphBlock
{
    /**
     * @param mixed[] $data
     */
    #[Override]
    protected function shouldHideBlock(Block $block, array $data): bool
    {
        return $this->siteService->isHome($data);
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
}
