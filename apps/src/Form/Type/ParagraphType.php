<?php

namespace Labstag\Form\Type;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class ParagraphType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'placeholder'   => new TranslatableMessage('Choisir le paragraphe'),
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
    #[Override]
    public function getBlockPrefix()
    {
        return 'paragraph';
    }
}
