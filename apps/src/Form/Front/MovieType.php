<?php

namespace Labstag\Form\Front;

use Labstag\Service\CategoryService;
use Labstag\Service\Imdb\MovieService;
use Labstag\Service\Imdb\SagaService;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
class MovieType extends AbstractType
{
    public function __construct(
        protected MovieService $movieService,
        protected CategoryService $categoryService,
        protected SagaService $sagaService,
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
        protected RequestStack $requestStack,
    )
    {
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        unset($options);
        $formBuilder->add('title', TextType::class, [
                'required' => false,
            ]);
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
                'choices'  => $this->categoryService->getCategoryMovieForForm(),
            ]
        );
        $formBuilder->add(
            'sagas',
            ChoiceType::class,
            [
                'required' => false,
                'choices'  => $this->sagaService->getSagaForForm(),
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
                    $this->translator->trans($title->getMessage(), $title->getParameters())       => 'title',
                    $this->translator->trans(
                        $releaseDate->getMessage(),
                        $releaseDate->getParameters()
                    )                                                                                 => 'releaseDate',
                    $this->translator->trans($dateAdded->getMessage(), $dateAdded->getParameters())   => 'createdAt',
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
                    $this->translator->trans($ascending->getMessage(), $ascending->getParameters())   => 'ASC',
                    $this->translator->trans($descending->getMessage(), $descending->getParameters()) => 'DESC',
                ],
            ]
        );
        $formBuilder->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('Search'),
            ]);
        $formBuilder->add('reset', ResetType::class, [
                'label' => new TranslatableMessage('Reset'),
            ]);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $slug    = $request->attributes->get('slug');

        $optionsResolver->setDefaults(
            [
                'csrf_protection' => false,
                'action'          => $this->router->generate('front', [
                        'slug' => $slug,
                    ]),
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
