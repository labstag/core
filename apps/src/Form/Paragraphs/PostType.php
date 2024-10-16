<?php

namespace Labstag\Form\Paragraphs;

use Labstag\Entity\Post;
use Labstag\Lib\ParagraphAbstractTypeLib;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends ParagraphAbstractTypeLib
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->setEntity(Post::class);
        parent::buildForm($builder, $options);
    }
}
