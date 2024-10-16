<?php

namespace Labstag\Form\Paragraphs;

use Override;
use Labstag\Entity\Post;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends ParagraphAbstractTypeLib
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $this->setEntity(Post::class);
        parent::buildForm($formBuilder, $options);
    }
}
