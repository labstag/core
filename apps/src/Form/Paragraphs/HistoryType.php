<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\History;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class HistoryType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setEntity(History::class);
        parent::buildForm($builder, $options);
    }
}
