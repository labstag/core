<?php

namespace Labstag\Paragraph;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Paragraph\Html;
use Labstag\Field\WysiwygField;
use Labstag\Lib\ParagraphLib;
use Override;

class HtmlParagraph extends ParagraphLib
{
    #[Override]
    public function getEntity()
    {
        return Html::class;
    }

    #[Override]
    public function getFields(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        yield TextField::new('html.title', 'Titre');
        $wysiwygField = WysiwygField::new('html.description', 'Texte');
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
