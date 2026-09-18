<?php

namespace Labstag\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class PlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        unset($options);
        $builder->add(
            'title',
            TextType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Title'),
            ]
        );
        $builder->add(
            'family',
            TextType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Family'),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
