<?php

namespace Labstag\Form\Admin;

use Labstag\Service\IgdbService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class GameImportType extends AbstractType
{
    public function __construct(
        private IgdbService $igdbService,
    )
    {
    }

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
        $builder->add(
            'platform',
            ChoiceType::class,
            [
                'label'    => new TranslatableMessage('Platform'),
                'required' => false,
                'choices'  => array_merge([
                        '' => '',
                    ], $this->igdbService->getPlatformChoices(),),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
