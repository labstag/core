<?php

namespace Labstag\Form\Front;

use Labstag\Service\MovieService;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractType<mixed>
 */
class MovieType extends AbstractType
{
    public function __construct(
        protected MovieService $movieService,
        protected RequestStack $requestStack,
    )
    {
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        unset($options);
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
            'sagas',
            ChoiceType::class,
            [
                'required' => false,
                'choices'  => $this->movieService->getSagaForForm(),
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
        $title       = new TranslatableMessage('Title');
        $releaseDate = new TranslatableMessage('Release date');
        $dateAdded   = new TranslatableMessage('Date added');
        $formBuilder->add(
            'order',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Order'),
                'choices'  => [
                    $title->__toString()       => 'title',
                    $releaseDate->__toString() => 'releaseDate',
                    $dateAdded->__toString()   => 'createdAt',
                ],
            ]
        );
        $ascending  = new TranslatableMessage('Ascending');
        $descending = new TranslatableMessage('Descending');
        $formBuilder->add(
            'orderby',
            ChoiceType::class,
            [
                'required' => false,
                'label'    => new TranslatableMessage('Sort'),
                'choices'  => [
                    $ascending->__toString()  => 'ASC',
                    $descending->__toString() => 'DESC',
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

    public function configureOptions(OptionsResolver $optionsResolver): void
    {

        $optionsResolver->setDefaults(
            [
                'csrf_protection' => false,
                'action'          => $this->requestStack->getCurrentRequest()?->getPathInfo(),
                'method'          => 'GET',
                'data_class'      => null,
            ]
        );
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return '';
    }
}
