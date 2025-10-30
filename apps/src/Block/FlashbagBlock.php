<?php

namespace Labstag\Block;

use Override;

class FlashbagBlock extends SimpleBlockAbstract
{
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
