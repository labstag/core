<?php

namespace Labstag\Controller\Admin;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setActionLinkPublic('admin_saga_public');
        $this->actionsFactory->setActionLinkW3CValidator('admin_saga_w3c');
        $this->setLinkTmdbAction();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll();

        return $this->actionsFactory->show();
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
        $textField        = TextField::new('tmdb', new TranslatableMessage('Tmdb'));

        $wysiwygField = WysiwygField::new('description', new TranslatableMessage('Description'));
        $wysiwygField->hideOnIndex();

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [
                $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
                $this->crudFieldFactory->slugField(),
                $this->crudFieldFactory->titleField(),
                $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
                $textField,
                $wysiwygField,
                $this->moviesFieldForPage(self::getEntityFqcn(), $pageName),
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

    public function moviesField(): AssociationField
    {
        $associationField = AssociationField::new('movies', new TranslatableMessage('Movies'));
        $associationField->setTemplatePath('admin/field/movies.html.twig');

        return $associationField;
    }

    /**
     * Page-aware variant to avoid AssociationConfigurator errors on index/detail pages.
     * - On index/detail: always return a read-only CollectionField (count/list via template).
     * - On edit/new: only return an AssociationField if Doctrine metadata confirms the association,
     *   otherwise hide the field on forms (no-op for safety).
     */
    public function moviesFieldForPage(string $entityFqcn, string $pageName): AssociationField|CollectionField
    {
        $associationField = $this->moviesField();
        // Always safe on listing/detail pages: no AssociationField to configure
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL, 'index', 'detail'], true)
        ) {
            $associationField->hideOnForm();

            return $associationField;
        }

        // For edit/new pages, check the real Doctrine association
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;

        if ($metadata instanceof ClassMetadata && $metadata->hasAssociation('movies')) {
            $associationField->autocomplete();
            $associationField->setFormTypeOption('by_reference', false);

            return $associationField;
        }

        // No association: ensure nothing is rendered on the form
        $associationField->hideOnForm();

        return $associationField;
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

    public function updateAll(MessageBusInterface $messageBus): RedirectResponse
    {
        $repositoryAbstract              = $this->getRepository();
        $sagas                           = $repositoryAbstract->findAll();
        foreach ($sagas as $saga) {
            $messageBus->dispatch(new SagaMessage($saga->getId()));
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

    private function setLinkTmdbAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

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

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

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

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
