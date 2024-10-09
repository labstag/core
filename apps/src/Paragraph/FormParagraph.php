<?php

namespace Labstag\Paragraph;

use Labstag\Entity\Paragraph\Form;
use Labstag\Lib\ParagraphLib;
use Override;

class FormParagraph extends ParagraphLib
{
    #[Override]
    public function getEntity()
    {
        return Form::class;
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
