<?php

namespace Labstag\Form\Paragraph;

use Labstag\Form\Paragraph\Collection\CompetenceType;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class CompetencesType extends AbstractType
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
            'competences',
            CollectionType::class,
            [
                'entry_type'   => CompetenceType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => new TranslatableMessage('Competences'),
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
