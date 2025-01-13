<?php

namespace Labstag\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParagraphType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'placeholder'   => 'Choisir le paragraphe',
                'choices'       => [],
                'urlparagraphs' => null,
                'list'          => null,
                'add'           => null,
                'edit'          => null,
                'delete'        => null,
            ]
        );
    }

    /**
     * @return string
     */
    #[\Override]
    public function getBlockPrefix()
    {
        return 'paragraph';
    }
}
