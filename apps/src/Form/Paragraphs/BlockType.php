<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Block;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class BlockType extends ParagraphAbstractTypeLib
{
    #[\Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Block::class);
        parent::buildForm($formBuilder, $options);
    }
}
