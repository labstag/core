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

    #[Override]
    public function getName(): TranslatableMessage
    {
        return new TranslatableMessage('Paragraphs');
    }

    #[Override]
    public function getType(): string
    {
        return 'paragraphs';
    }
}
