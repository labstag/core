<?php

namespace Labstag\Controller\Admin;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Saga;
use Labstag\Field\WysiwygField;
use Labstag\Message\SagaAllMessage;
use Labstag\Message\SagaMessage;
use Labstag\Service\FileService;
use Labstag\Service\JsonPaginatorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Translation\TranslatableMessage;

class SagaCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setLinkTmdbAction();
        $this->setUpdateAction();
        $this->actionsFactory->setActionUpdateAll();
        $this->setShowAllRecommandations();

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
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
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
        if (Crud::PAGE_DETAIL === $pageName) {
            $this->crudFieldFactory->addTab(
                'recommandations',
                FormField::addTab(new TranslatableMessage('Recommandations'))
            );

            $textField = TextField::new('id', new TranslatableMessage('Recommandations'));
            $textField->setTemplatePath('admin/field/recommandations.html.twig');
            $this->crudFieldFactory->addFieldsToTab('recommandations', [$textField]);
        }

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    public static function getEntityFqcn(): string
    {
        return Saga::class;
    }

    public function jsonSaga(AdminContext $adminContext, TheMovieDbApi $theMovieDbApi): JsonResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entityId);

        $details = $theMovieDbApi->getDetailsSaga($saga);

        return new JsonResponse($details);
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
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL], true)
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

    public function recommandationsAll(
        FileService $fileService,
        JsonPaginatorService $jsonPaginatorService,
    ): Response
    {
        $file         = $fileService->getFileInAdapter('private', 'recommandations-saga.json');
        if (!is_file($file)) {
            return $this->redirectToRoute('admin_saga_index');
        }

        $pagination = $jsonPaginatorService->paginate($file, 'title');

        return $this->render(
            'admin/saga/recommandations.html.twig',
            ['pagination' => $pagination]
        );
    }

    public function setShowAllRecommandations(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('recommandationsAll', new TranslatableMessage('all recommendations'), 'fas fa-terminal');
        $action->displayAsLink();
        $action->linkToCrudAction('recommandationsAll');
        $action->createAsGlobalAction();

        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }

    public function tmdb(AdminContext $adminContext): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entityId);

        return $this->redirect('https://www.themoviedb.org/collection/' . $saga->getTmdb());
    }

    public function updateAll(MessageBusInterface $messageBus): RedirectResponse
    {
        $messageBus->dispatch(new SagaAllMessage());

        return $this->redirectToRoute('admin_saga_index');
    }

    public function updateSaga(
        AdminContext $adminContext,
        Request $request,
        MessageBusInterface $messageBus,
    ): RedirectResponse
    {
        $entityId = $adminContext->getRequest()->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $saga                            = $repositoryAbstract->find($entityId);
        $messageBus->dispatch(new SagaMessage($saga->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_saga_index');
    }

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateSaga', new TranslatableMessage('Update'));
        $action->linkToCrudAction('updateSaga');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('jsonSaga', new TranslatableMessage('Json'));
        $action->linkToCrudAction('jsonSaga');
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
