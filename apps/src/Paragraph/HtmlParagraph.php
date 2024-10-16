<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Field\WysiwygField;
use Labstag\Lib\ParagraphLib;
use Override;

class HtmlParagraph extends ParagraphLib
{
    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        yield TextField::new('title', 'Titre');
        $wysiwygField = WysiwygField::new('content', 'Texte');
        yield $wysiwygField;
    }

    #[Override]
    public function getName(): string
    {
        return 'HTML';
    }

    #[Override]
    public function getType(): string
    {
        return 'html';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
