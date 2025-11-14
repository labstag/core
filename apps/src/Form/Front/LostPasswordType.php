<?php

namespace Labstag\Form\Front;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class LostPasswordType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        unset($options);
        $formBuilder->add(
            'find',
            TextType::class,
            [
                'required' => true,
                'label'    => new TranslatableMessage('Email / Pseudo'),
            ]
        );
        $formBuilder->add('submit', SubmitType::class);
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([]);
    }
}
