<?php

namespace Labstag\Form\Paragraphs;

use Override;
use Labstag\Entity\Edito;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class EditoType extends ParagraphAbstractTypeLib
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Edito::class);
        parent::buildForm($formBuilder, $options);
    }
}
