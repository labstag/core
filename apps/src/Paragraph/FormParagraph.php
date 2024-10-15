<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph;
use Labstag\Lib\ParagraphLib;
use Override;

class FormParagraph extends ParagraphLib
{
    #[Override]
    public function getFieldsEA(Paragraph $paragraph): iterable
    {
        unset($paragraph);

        return [];
    }

    #[Override]
    public function getName(): string
    {
        return 'Formulaire';
    }

    #[Override]
    public function getType(): string
    {
        return 'form';
    }

    #[Override]
    public function useIn(): array
    {
        return $this->useInAll();
    }
}
