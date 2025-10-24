<?php

namespace Labstag\Block;

use Labstag\Block\Abstract\AbstractParagraphBlock;
use Override;

class ParagraphsBlock extends AbstractParagraphBlock
{
    // Utilise l'implémentation par défaut d'AbstractParagraphBlock

    #[Override]
    public function getName(): string
    {
        return 'Paragraphs';
    }

    #[Override]
    public function getType(): string
    {
        return 'paragraphs';
    }
}
