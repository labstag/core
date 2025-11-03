<?php

namespace Labstag\Block;

use Labstag\Entity\ParagraphsBlock as EntityParagraphsBlock;
use Override;

class ParagraphsBlock extends ParagraphBlockAbstract
{
    public function getClass(): string
    {
        return EntityParagraphsBlock::class;
    }

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
