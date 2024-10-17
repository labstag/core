<?php

namespace Labstag\Form\Paragraphs;

use Override;
use Labstag\Entity\Memo;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class MemoType extends ParagraphAbstractTypeLib
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Memo::class);
        parent::buildForm($formBuilder, $options);
    }
}
