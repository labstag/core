<?php

namespace Labstag\Block;

use Labstag\Block\Abstract\AbstractSimpleBlock;
use Override;

class FlashbagBlock extends AbstractSimpleBlock
{
    // Utilise l'implémentation par défaut d'AbstractSimpleBlock

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
