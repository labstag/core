<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Labstag\Lib\ParagraphLib;
use Override;

class TextParagraph extends ParagraphLib
{
    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);
        $wysiwygField = WysiwygField::new('content', 'Texte');

        yield $wysiwygField;
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
