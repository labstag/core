<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Chapter;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class ChapterType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setEntity(Chapter::class);
        parent::buildForm($builder, $options);
    }
}
