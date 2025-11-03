<?php

namespace Labstag\Block;

use Override;

class ParagraphsBlock extends ParagraphBlockAbstract
{
    // Utilise l'implémentation par défaut d'ParagraphBlockAbstract

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
