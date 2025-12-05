<?php

namespace Labstag\Form\Front;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
class ChangePasswordType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        unset($options);
        $formBuilder->add(
            'plainPassword',
            RepeatedType::class,
            [
                'type'            => PasswordType::class,
                'invalid_message' => new TranslatableMessage('The password fields must match.'),
                'mapped'          => false,
                'first_options'   => [
                    'label'       => new TranslatableMessage('New password'),
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => new TranslatableMessage('Please enter a password'),
                            ]
                        ),
                        new Length(
                            [
                                'min'        => 8,
                                'minMessage' => new TranslatableMessage(
                                    'Your password should be at least {{ limit }} characters',
                                    ['{{ limit }}' => 8]
                                ),
                            ]
                        ),
                    ],
                ],
                'second_options'  => [
                    'label' => new TranslatableMessage('Confirm password'),
                ],
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
