<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Meta;
use Labstag\Entity\Serie;
use Labstag\Field\WysiwygField;
use Labstag\Message\SerieMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class SerieCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_serie_w3c', 'admin_serie_public');

        $action = $this->setLinkImdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $action = $this->setLinkTmdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $action = $this->setUpdateAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);
        $this->configureActionsUpdateImage();

        $action = Action::new('updateAll', new TranslatableMessage('Update all'), 'fas fa-sync-alt');
        $action->displayAsLink();
        $action->linkToCrudAction('updateAll');
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Serie'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Series'));
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $textField = TextField::new('imdb', new TranslatableMessage('Imdb'));
        $textField->hideOnIndex();

        $tmdField = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $tmdField->hideOnIndex();

        $certificationField = TextField::new('certification', new TranslatableMessage('Certification'));
        $certificationField->hideOnIndex();

        $choiceField = ChoiceField::new('countries', new TranslatableMessage('Countries'));
        $choiceField->setChoices(array_flip(Countries::getNames()));
        $choiceField->allowMultipleChoices();
        $choiceField->renderExpanded(false);

        $collectionField = CollectionField::new('seasons', new TranslatableMessage('Seasons'));
        $collectionField->setTemplatePath('admin/field/seasons.html.twig');
        $collectionField->hideOnForm();

        $runtimeField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $runtimeField->setTemplatePath('admin/field/runtime-serie.html.twig');
        $runtimeField->hideOnForm();
        $runtimeField->hideOnIndex();

        $trailerField = TextField::new('trailer', new TranslatableMessage('Trailer'));
        $trailerField->hideOnIndex();

        $wysiwygField = WysiwygField::new('citation', new TranslatableMessage('Citation'));
        $wysiwygField->hideOnIndex();

        $descriptionField = WysiwygField::new('description', new TranslatableMessage('Description'));
        $descriptionField->hideOnIndex();

        $booleanField = $this->crudFieldFactory->booleanField('file', (string) new TranslatableMessage('File'));
        $booleanField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $this->crudFieldFactory->booleanField(
                    'inProduction',
                    (string) new TranslatableMessage('in Production')
                ),
                $textField,
                $tmdField,
                $certificationField,
                DateField::new('releaseDate', new TranslatableMessage('Release date')),
                DateField::new('lastReleaseDate', new TranslatableMessage('Last release date')),
                $choiceField,
                $runtimeField,
                NumberField::new('evaluation', new TranslatableMessage('Evaluation'))->hideOnIndex(),
                IntegerField::new('votes', new TranslatableMessage('Votes'))->hideOnIndex(),
                $trailerField,
                $wysiwygField,
                $descriptionField,
                $this->crudFieldFactory->categoriesField('serie'),
                $collectionField,
                $booleanField,
                $this->crudFieldFactory->booleanField('adult', (string) new TranslatableMessage('Adult')),
            ]
        );
        $this->crudFieldFactory->setTabSEO();
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);

        $filters->add('releaseDate');
        $filters->add('countries');
        $filters->add('inProduction');

        $this->crudFieldFactory->addFilterCategories($filters, 'serie');

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Serie
    {
        $serie = new $entityFqcn();
        $meta  = new Meta();
        $serie->setMeta($meta);

        return $serie;
    }

    public static function getEntityFqcn(): string
    {
        return Serie::class;
    }

    #[Route('/admin/serie/{entity}/imdb', name: 'admin_serie_imdb')]
    public function imdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $serviceEntityRepositoryAbstract->find($entity);
        if (empty($serie->getImdb())) {
            return $this->redirectToRoute('admin_serie_index');
        }

        return $this->redirect('https://www.imdb.com/title/' . $serie->getImdb() . '/');
    }

    #[Route('/admin/serie/{entity}/public', name: 'admin_serie_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $serviceEntityRepositoryAbstract->find($entity);

        return $this->publicLink($serie);
    }

    #[Route('/admin/serie/{entity}/tmdb', name: 'admin_serie_tmdb')]
    public function tmdb(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $serviceEntityRepositoryAbstract->find($entity);

        return $this->redirect('https://www.themoviedb.org/tv/' . $serie->getTmdb());
    }

    #[Route('/admin/serie/{entity}/update', name: 'admin_serie_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $serviceEntityRepositoryAbstract->find($entity);
        $messageBus->dispatch(new SerieMessage($serie->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_serie_index');
    }

    public function updateAll(MessageBusInterface $messageBus): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $series                          = $serviceEntityRepositoryAbstract->findAll();
        foreach ($series as $serie) {
            $messageBus->dispatch(new SerieMessage($serie->getId()));
        }

        return $this->redirectToRoute('admin_serie_index');
    }

    #[Route('/admin/serie/{entity}/w3c', name: 'admin_serie_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $serviceEntityRepositoryAbstract->find($entity);

        return $this->linkw3CValidator($serie);
    }

    private function configureActionsUpdateImage(): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $request->query->get('action', null);
    }

    private function setLinkImdbAction(): Action
    {
        $action = Action::new('imdb', new TranslatableMessage('IMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Serie $serie): string => $this->generateUrl(
                'admin_serie_imdb',
                [
                    'entity' => $serie->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }

    private function setLinkTmdbAction(): Action
    {
        $action = Action::new('tmdb', new TranslatableMessage('TMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Serie $serie): string => $this->generateUrl(
                'admin_serie_tmdb',
                [
                    'entity' => $serie->getId(),
                ]
            )
        );

        return $action;
    }

    private function setUpdateAction(): Action
    {
        $action = Action::new('update', new TranslatableMessage('Update'));
        $action->linkToUrl(
            fn (Serie $serie): string => $this->generateUrl(
                'admin_serie_update',
                [
                    'entity' => $serie->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
