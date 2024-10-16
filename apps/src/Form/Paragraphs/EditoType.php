<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Edito;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class EditoType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setEntity(Edito::class);
        parent::buildForm($builder, $options);
    }
}
