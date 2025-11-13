<?php

namespace Labstag\Form\Paragraph;

use Labstag\Form\Paragraph\Collection\SkillsType;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class ExperienceType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add(
            'title',
            TextType::class,
            [
                'label' => new TranslatableMessage('Title'),
            ]
        );
        $formBuilder->add(
            'entreprise',
            TextType::class,
            [
                'label' => new TranslatableMessage('Company'),
            ]
        );
        $formBuilder->add(
            'yearStart',
            DateType::class,
            [
                'label'  => new TranslatableMessage('Start Year'),
                'widget' => 'single_text',
                'input'  => 'string',
            ]
        );
        $formBuilder->add(
            'yearEnd',
            DateType::class,
            [
                'label'    => new TranslatableMessage('End Year'),
                'widget'   => 'single_text',
                'input'    => 'string',
                'required' => false,
            ]
        );
        $formBuilder->add(
            'end',
            CheckboxType::class,
            [
                'label'    => new TranslatableMessage('In Progress'),
                'required' => false,
            ]
        );

        $formBuilder->add(
            'description',
            TextareaType::class,
            [
                'attr'     => [
                    'rows'  => 40,
                    'class' => 'wysiwyg',
                ],
                'label'    => new TranslatableMessage('Description'),
                'required' => false,
            ]
        );
        $formBuilder->add(
            'skills',
            CollectionType::class,
            [
                'entry_type'   => SkillsType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => new TranslatableMessage('Skills'),
            ]
        );

        unset($options);
    }

    #[Override]
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([]);
    }
}
