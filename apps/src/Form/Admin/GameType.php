<?php

namespace Labstag\Form\Admin;

use Labstag\Service\IgdbService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class GameType extends AbstractType
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
            'number',
            IntegerType::class,
            [
                'label'    => new TranslatableMessage('IGDB ID'),
                'required' => false,
            ]
        );
        $builder->add(
            'title',
            TextType::class,
            [
                'label'    => new TranslatableMessage('Title'),
                'required' => false,
            ]
        );
        $builder->add(
            'franchise',
            TextType::class,
            [
                'label'    => new TranslatableMessage('Franchise'),
                'required' => false,
            ]
        );
        $builder->add(
            'platform',
            ChoiceType::class,
            [
                'label'    => new TranslatableMessage('Platform'),
                'required' => false,
                'choices'  => array_merge(
                    ['' => ''],
                    $this->igdbService->getPlatformChoices(),
                ),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
