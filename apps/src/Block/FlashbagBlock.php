<?php

namespace Labstag\Block;

use Labstag\Entity\FlashbagBlock as EntityFlashbagBlock;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class FlashbagBlock extends SimpleBlockAbstract
{
    public function getClass(): string
    {
        return EntityFlashbagBlock::class;
    }

    #[Override]
    public function getName(): string
    {
        return (string) new TranslatableMessage('Flashbag');
    }

    #[Override]
    public function getType(): string
    {
        return 'flashbag';
    }
}
