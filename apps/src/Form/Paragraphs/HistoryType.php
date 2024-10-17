<?php

namespace Labstag\Form\Paragraphs;

use Override;
use Labstag\Entity\History;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class HistoryType extends ParagraphAbstractTypeLib
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(History::class);
        parent::buildForm($formBuilder, $options);
    }
}
