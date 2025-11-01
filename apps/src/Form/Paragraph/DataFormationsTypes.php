<?php

namespace Labstag\Form\Paragraph;

use Labstag\Form\Paragraph\Collection\FormationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class DataFormationsTypes extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'formations',
            CollectionType::class,
            [
                'entry_type'   => FormationType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => new TranslatableMessage('Formations'),
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
                'entry_type'    => FormationType::class,
            ]
        );
    }
}
