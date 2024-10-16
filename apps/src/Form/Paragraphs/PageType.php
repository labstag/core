<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Page;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class PageType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setEntity(Page::class);
        parent::buildForm($builder, $options);
    }
}
