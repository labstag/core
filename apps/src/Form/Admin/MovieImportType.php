<?php

namespace Labstag\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class MovieImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        unset($options);
        $builder->add(
            'file',
            FileType::class,
            [
                'label'    => new TranslatableMessage('File'),
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
