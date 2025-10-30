<?php

namespace Labstag\Form;

use Labstag\Entity\Link;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class LinkType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add(
            'title',
            TextType::class,
            ['label' => 'Titre']
        );
        $formBuilder->add(
            'url',
            TextType::class,
            ['label' => 'Url']
        );
        $formBuilder->add(
            'blank',
            CheckboxType::class,
            [
                'label'    => 'Lien dans une nouvelle fenêtre',
                'required' => false,
            ]
        );
        $formBuilder->add(
            'classes',
            TextType::class,
            [
                'label'    => 'Classes',
                'required' => false,
            ]
        );

        unset($options);
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'data_class' => Link::class,
            ]
        );
    }
}
