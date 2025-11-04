<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Saga;
use Labstag\Field\WysiwygField;
use Labstag\Message\SagaMessage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class SagaCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_saga_w3c', 'admin_saga_public');

        $this->setEditDetail($actions);
        $this->configureActionsBtn($actions);
        $action = $this->setLinkTmdbAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $action = $this->setUpdateAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['title' => 'ASC']
        );
        $crud->setEntityLabelInSingular(new TranslatableMessage('Saga'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Sagas'));

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $textField       = TextField::new('tmdb', new TranslatableMessage('Tmdb'));
        $collectionField = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $collectionField->onlyOnIndex();
        $collectionField->formatValue(fn ($value): int => count($value));

        $wysiwygField = WysiwygField::new('description', new TranslatableMessage('Description'));
        $wysiwygField->hideOnIndex();

        $movieField2 = CollectionField::new('movies', new TranslatableMessage('Movies'));
        $movieField2->setTemplatePath('admin/field/movies.html.twig');
        $movieField2->onlyOnDetail();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $textField,
                $collectionField,
                $wysiwygField,
                $movieField2,
            ]
        );

        $this->crudFieldFactory->setTabSEO();
        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Saga::class;
    }

    #[Route('/admin/saga/{entity}/public', name: 'admin_saga_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entity);

        return $this->publicLink($saga);
    }

    #[Route('/admin/saga/{entity}/imdb', name: 'admin_saga_tmdb')]
    public function tmdb(string $entity): RedirectResponse
    {
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entity);

        return $this->redirect('https://www.themoviedb.org/collection/' . $saga->getTmdb());
    }

    #[Route('/admin/saga/{entity}/update', name: 'admin_saga_update')]
    public function update(string $entity, Request $request, MessageBusInterface $messageBus): RedirectResponse
    {
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entity);
        $messageBus->dispatch(new SagaMessage($saga->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_saga_index');
    }

    #[Route('/admin/saga/{entity}/w3c', name: 'admin_saga_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entity);

        return $this->linkw3CValidator($saga);
    }

    private function setLinkTmdbAction(): Action
    {
        $action = Action::new('tmdb', new TranslatableMessage('TMDB Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToUrl(
            fn (Saga $saga): string => $this->generateUrl(
                'admin_saga_tmdb',
                [
                    'entity' => $saga->getId(),
                ]
            )
        );

        return $action;
    }

    private function setUpdateAction(): Action
    {
        $action = Action::new('update', new TranslatableMessage('Update'));
        $action->linkToUrl(
            fn (Saga $saga): string => $this->generateUrl(
                'admin_saga_update',
                [
                    'entity' => $saga->getId(),
                ]
            )
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        return $action;
    }
}
