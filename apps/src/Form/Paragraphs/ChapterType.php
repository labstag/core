<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Chapter;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class ChapterType extends ParagraphAbstractTypeLib
{
    #[\Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Chapter::class);
        parent::buildForm($formBuilder, $options);
    }
}
