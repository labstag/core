<?php

namespace Labstag\Form\Paragraph\Collection;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class SkillsType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add('position', HiddenType::class);
        $formBuilder->add('icon', TextType::class, [
                'label' => new TranslatableMessage('Icon'),
            ]);
        $formBuilder->add('title', TextType::class, [
                'label' => new TranslatableMessage('Title'),
            ]);

        unset($options);
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
                'label' => false,
            ]);
    }
}
