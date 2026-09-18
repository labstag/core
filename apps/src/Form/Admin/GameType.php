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

        $msg1  = new TranslatableMessage('Main Game');
        $msg2  = new TranslatableMessage('DLC');
        $msg3  = new TranslatableMessage('Expansion');
        $msg4  = new TranslatableMessage('Bundle');
        $msg5  = new TranslatableMessage('Standalone Expansion');
        $msg6  = new TranslatableMessage('Mod');
        $msg7  = new TranslatableMessage('Episode');
        $msg8  = new TranslatableMessage('Season');
        $msg9  = new TranslatableMessage('Remake');
        $msg10 = new TranslatableMessage('Remaster');
        $msg11 = new TranslatableMessage('Expanded Game');
        $msg12 = new TranslatableMessage('Port');
        $msg13 = new TranslatableMessage('Fork');
        $msg14 = new TranslatableMessage('Pack / Addon');
        $msg15 = new TranslatableMessage('Update');
        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label'    => new TranslatableMessage('Type'),
                'required' => false,
                'choices'  => [
                    $this->translator->trans($msg1->getMessage())  => 0,
                    $this->translator->trans($msg2->getMessage())  => 1,
                    $this->translator->trans($msg3->getMessage())  => 2,
                    $this->translator->trans($msg4->getMessage())  => 3,
                    $this->translator->trans($msg5->getMessage())  => 4,
                    $this->translator->trans($msg6->getMessage())  => 5,
                    $this->translator->trans($msg7->getMessage())  => 6,
                    $this->translator->trans($msg8->getMessage())  => 7,
                    $this->translator->trans($msg9->getMessage())  => 8,
                    $this->translator->trans($msg10->getMessage()) => 9,
                    $this->translator->trans($msg11->getMessage()) => 10,
                    $this->translator->trans($msg12->getMessage()) => 11,
                    $this->translator->trans($msg13->getMessage()) => 12,
                    $this->translator->trans($msg14->getMessage()) => 13,
                    $this->translator->trans($msg15->getMessage()) => 14,
                ],
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
