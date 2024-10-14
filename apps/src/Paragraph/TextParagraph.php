<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Entity\Paragraph\Text;
use Labstag\Lib\ParagraphLib;
use Override;

class TextParagraph extends ParagraphLib
{
    #[Override]
    public function getEntity()
    {
        return Text::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Texte';
    }

    #[Override]
    public function getType(): string
    {
        return 'text';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
