<?php

namespace Labstag\Form\Paragraph\Collection;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class FormationType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add(
            'title',
            TextType::class,
            [
                'label' => new TranslatableMessage('Title'),
            ]
        );
        $formBuilder->add(
            'year',
            TextType::class,
            [
                'label' => new TranslatableMessage('Year'),
            ]
        );
        $formBuilder->add(
            'place',
            TextType::class,
            [
                'label' => new TranslatableMessage('Place'),
            ]
        );

        unset($options);
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([]);
    }
}
