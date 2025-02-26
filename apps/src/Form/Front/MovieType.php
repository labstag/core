<?php

namespace Labstag\Form\Front;

use Labstag\Service\MovieService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class MovieType extends AbstractType
{
    public function __construct(
        protected MovieService $movieService
    )
    {

    }

    public function buildForm(
        FormBuilderInterface $formBuilder,
        array $options
    ): void
    {
        $formBuilder->add(
            'title',
            TextType::class,
            ['required' => false]
        );
        $formBuilder->add(
            'country',
            ChoiceType::class,
            [
                'required' => false,
                'choices'  => $this->movieService->getCountryForForm(),
            ]
        );
        $formBuilder->add(
            'categories',
            ChoiceType::class,
            [
                'required' => false,
                'choices'  => $this->movieService->getCategoryForForm(),
            ]
        );
        $formBuilder->add(
            'year',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Year'),
                'choices'  => $this->movieService->getYearForForm(),
            ]
        );
        $formBuilder->add(
            'order',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Order'),
                'choices'  => [
                    'Titre'        => 'title',
                    'Année'        => 'year',
                    "Date d'ajout" => 'createdAt',
                ],
            ]
        );
        $formBuilder->add(
            'orderby',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Sort'),
                'choices'  => [
                    'Croissant'   => 'ASC',
                    'Décroissant' => 'DESC',
                ],
            ]
        );
        $formBuilder->add(
            'submit',
            SubmitType::class,
            [
                'label' => new TranslatableMessage('Search'),
            ]
        );
        $formBuilder->add(
            'reset',
            ResetType::class,
            [
                'label' => new TranslatableMessage('Reset'),
            ]
        );
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(
            [
                'csrf_protection' => false,
                'action'          => '/mes-derniers-films-vus',
                'method'          => 'GET',
                'data_class'      => null,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
