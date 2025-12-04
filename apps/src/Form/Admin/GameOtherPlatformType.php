<?php

namespace Labstag\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class GameOtherPlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'platforms',
            ChoiceType::class,
            [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'label'    => new TranslatableMessage('Platforms'),
                'choices'  => $this->setPlatform($options['platforms']),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                'platforms' => [],
            ]);
    }

    private function setPlatform(array $platforms): array
    {
        $choices = [];
        foreach ($platforms as $platform) {
            $choices[$platform->getTitle()] = $platform->getId();
        }

        return $choices;
    }
}
