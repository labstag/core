<?php

namespace Labstag\Form\Admin;

use Labstag\Service\IgdbService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameType extends AbstractType
{
    public function __construct(
        private IgdbService $igdbService,
        private TranslatorInterface $translator,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        unset($options);
        $builder->add(
            'number',
            TextType::class,
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
            'type',
            ChoiceType::class,
            [
                'label'    => new TranslatableMessage('Type'),
                'required' => false,
                'choices'  => [
                    $this->translator->trans(new TranslatableMessage('Main Game'))            => 0,
                    $this->translator->trans(new TranslatableMessage('DLC'))                  => 1,
                    $this->translator->trans(new TranslatableMessage('Expansion'))            => 2,
                    $this->translator->trans(new TranslatableMessage('Bundle'))               => 3,
                    $this->translator->trans(new TranslatableMessage('Standalone Expansion')) => 4,
                    $this->translator->trans(new TranslatableMessage('Mod'))                  => 5,
                    $this->translator->trans(new TranslatableMessage('Episode'))              => 6,
                    $this->translator->trans(new TranslatableMessage('Season'))               => 7,
                    $this->translator->trans(new TranslatableMessage('Remake'))               => 8,
                    $this->translator->trans(new TranslatableMessage('Remaster'))             => 9,
                    $this->translator->trans(new TranslatableMessage('Expanded Game'))        => 10,
                    $this->translator->trans(new TranslatableMessage('Port'))                 => 11,
                    $this->translator->trans(new TranslatableMessage('Fork'))                 => 12,
                    $this->translator->trans(new TranslatableMessage('Pack / Addon'))         => 13,
                    $this->translator->trans(new TranslatableMessage('Update'))               => 14,
                ],
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
