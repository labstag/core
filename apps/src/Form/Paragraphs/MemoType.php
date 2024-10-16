<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Memo;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class MemoType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setEntity(Memo::class);
        parent::buildForm($builder, $options);
    }
}
