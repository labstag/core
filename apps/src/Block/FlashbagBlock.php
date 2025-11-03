<?php

namespace Labstag\Block;

use Labstag\Entity\FlashbagBlock as EntityFlashbagBlock;
use Override;

class FlashbagBlock extends SimpleBlockAbstract
{
    public function getClass(): string
    {
        return EntityFlashbagBlock::class;
    }

    // Utilise l'implémentation par défaut d'SimpleBlockAbstract

    #[Override]
    public function getName(): string
    {
        return 'Flashbag';
    }

    #[Override]
    public function getType(): string
    {
        return 'flashbag';
    }
}
