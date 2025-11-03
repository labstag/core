<?php

namespace Labstag\Form\Block;

use Labstag\Form\Block\Collection\LinkType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class DataLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'links',
            CollectionType::class,
            [
                'entry_type'   => LinkType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => new TranslatableMessage('Links'),
            ]
        );
        unset($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'allow_add'     => true,
                'allow_delete'  => true,
                'delete_empty'  => true,
                'entry_options' => ['label' => true],
                'entry_type'    => LinkType::class,
            ]
        );
    }
}
