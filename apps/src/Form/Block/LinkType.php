<?php

namespace Labstag\Form\Block;

use Labstag\Form\Block\Collection\LinkType as CollectionLinkType;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class LinkType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add('position', HiddenType::class);
        $formBuilder->add(
            'title',
            TextType::class,
            [
                'label' => new TranslatableMessage('Title'),
            ]
        );
        $formBuilder->add(
            'url',
            TextType::class,
            [
                'label' => new TranslatableMessage('Url'),
            ]
        );
        $formBuilder->add(
            'blank',
            CheckboxType::class,
            [
                'label'    => new TranslatableMessage('Open link in new window'),
                'required' => false,
            ]
        );
        $formBuilder->add(
            'classes',
            TextType::class,
            [
                'label'    => new TranslatableMessage('Classes'),
                'required' => false,
            ]
        );
        $formBuilder->add(
            'links',
            CollectionType::class,
            [
                'entry_type'   => CollectionLinkType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => false,
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
