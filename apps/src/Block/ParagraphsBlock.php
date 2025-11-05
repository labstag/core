<?php

namespace Labstag\Block;

use Labstag\Entity\ParagraphsBlock as EntityParagraphsBlock;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

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
        return (string) new TranslatableMessage('Paragraphs');
    }

    #[Override]
    public function getType(): string
    {
        return 'paragraphs';
    }
}
