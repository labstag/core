<?php

namespace Labstag\Form\Front;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class LoginType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        unset($options);
        $formBuilder->add(
            'username',
            TextType::class,
            [
                'required' => true,
                'label'    => new TranslatableMessage('Username / email'),
            ]
        );
        $formBuilder->add(
            'password',
            PasswordType::class,
            [
                'required' => true,
                'label'    => new TranslatableMessage('Password'),
            ]
        );
        $formBuilder->add(
            'remember_me',
            CheckboxType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Remember me'),
            ]
        );
        $formBuilder->add('_target_path', HiddenType::class, []);
        $formBuilder->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('Login'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([]);
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'login';
    }
}
