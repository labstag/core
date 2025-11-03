<?php

namespace Labstag\Form\Paragraph;

use Labstag\Form\Paragraph\Collection\ExperienceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class DataExperiencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'experiences',
            CollectionType::class,
            [
                'entry_type'   => ExperienceType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => new TranslatableMessage('Experiences'),
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
                'entry_type'    => ExperienceType::class,
            ]
        );
    }
}
