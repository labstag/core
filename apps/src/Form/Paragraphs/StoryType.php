<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Story;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;
use Override;

class StoryType extends ParagraphAbstractTypeLib
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Story::class);
        parent::buildForm($formBuilder, $options);
    }
}
