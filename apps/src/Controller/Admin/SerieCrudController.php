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
        yield $this->addTabPrincipal();
        foreach ($this->crudFieldFactory->baseIdentitySet(
            $pageName,
            self::getEntityFqcn(),
            withSlug: true
        ) as $field) {
            yield $field;
        }

        yield $this->crudFieldFactory->booleanField('inProduction', (string) new TranslatableMessage('in Production'));
        yield TextField::new('imdb', new TranslatableMessage('Imdb'))->hideOnIndex();
        yield TextField::new('tmdb', new TranslatableMessage('Tmdb'))->hideOnIndex();
        yield TextField::new('certification', new TranslatableMessage('Certification'))->hideOnIndex();
        yield DateField::new('releaseDate', new TranslatableMessage('Release date'));
        yield DateField::new('lastReleaseDate', new TranslatableMessage('Last release date'));
        $choiceField = ChoiceField::new('countries', new TranslatableMessage('Countries'));
        $choiceField->setChoices(array_flip(Countries::getNames()));
        $choiceField->allowMultipleChoices();
        $choiceField->renderExpanded(false);
        yield $choiceField;
        $serieCollectionField = CollectionField::new('seasons', new TranslatableMessage('Seasons'));
        $serieCollectionField->setTemplatePath('admin/field/seasons.html.twig');
        $serieCollectionField->hideOnForm();

        $episodeCollectionField = CollectionField::new('runtime', new TranslatableMessage('Runtime'));
        $episodeCollectionField->setTemplatePath('admin/field/runtime-serie.html.twig');
        $episodeCollectionField->hideOnForm();
        yield $episodeCollectionField;
        yield from [
            NumberField::new('evaluation', new TranslatableMessage('Evaluation')),
            IntegerField::new('votes', new TranslatableMessage('Votes')),
            TextField::new('trailer', new TranslatableMessage('Trailer'))->hideOnIndex(),
            WysiwygField::new('citation', new TranslatableMessage('Citation'))->hideOnIndex(),
            WysiwygField::new('description', new TranslatableMessage('Description'))->hideOnIndex(),
            $this->crudFieldFactory->categoriesField('serie'),
            $serieCollectionField,
            // image field déjà incluse dans baseIdentitySet
            $this->crudFieldFactory->booleanField('file', (string) new TranslatableMessage('File'))->hideOnIndex(),
            $this->crudFieldFactory->booleanField('adult', (string) new TranslatableMessage('Adult')),
        ];
        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);

        $filters->add('releaseDate');
        $filters->add('countries');

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
        $ServiceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $ServiceEntityRepositoryAbstract->find($entity);
        if (empty($serie->getImdb())) {
            return $this->redirectToRoute('admin_serie_index');
        }

        return $this->redirect('https://www.imdb.com/title/' . $serie->getImdb() . '/');
    }

    #[Route('/admin/serie/{entity}/public', name: 'admin_serie_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $ServiceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $ServiceEntityRepositoryAbstract->find($entity);

        return $this->publicLink($serie);
    }

    #[Route('/admin/serie/{entity}/tmdb', name: 'admin_serie_tmdb')]
    public function tmdb(string $entity): RedirectResponse
    {
        $ServiceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $ServiceEntityRepositoryAbstract->find($entity);

        return $this->redirect('https://www.themoviedb.org/tv/' . $serie->getTmdb());
    }

    #[Route('/admin/serie/{entity}/update', name: 'admin_serie_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $ServiceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $ServiceEntityRepositoryAbstract->find($entity);
        $messageBus->dispatch(new SerieMessage($serie->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_serie_index');
    }

    #[Route('/admin/serie/{entity}/w3c', name: 'admin_serie_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $ServiceEntityRepositoryAbstract = $this->getRepository();
        $serie                           = $ServiceEntityRepositoryAbstract->find($entity);

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
