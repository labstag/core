<?php

namespace Labstag\Form\Front;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class ContactType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        unset($options);
        $formBuilder->add(
            'firstname',
            TextType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('First name'),
            ]
        );
        $formBuilder->add(
            'lastname',
            TextType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Last name'),
            ]
        );
        $formBuilder->add(
            'content',
            TextareaType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Content'),
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
