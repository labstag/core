<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Page;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;
use Override;

class PageType extends ParagraphAbstractTypeLib
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Page::class);
        parent::buildForm($formBuilder, $options);
    }
}
