<?php

namespace Labstag\Lib;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphsField;
use Labstag\Repository\ParagraphRepository;
use Labstag\Repository\TagRepository;
use Labstag\Service\FileService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Form\Type\VichImageType;

abstract class AbstractCrudControllerLib extends AbstractCrudController
{
    public function __construct(
        protected TagRepository $tagRepository,
        protected FileService $fileService,
        protected SiteService $siteService,
        protected ParagraphService $paragraphService,
        protected WorkflowService $workflowService,
        protected RequestStack $requestStack,
        protected UserService $userService
    )
    {
    }

    public function addFieldImageUpload(string $type, string $pageName)
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $imageField = TextField::new($type.'File');
            $imageField->setFormType(VichImageType::class);

            return $imageField;
        }

        $entity     = $this->getEntityFqcn();
        $basePath   = $this->fileService->getBasePath($entity, $type.'File');
        $imageField = ImageField::new($type);
        $imageField->setBasePath($basePath);

        return $imageField;
    }

    public function addParagraph(
        AdminContext $adminContext
    )
    {
        $request  = $adminContext->getRequest();
        $entityId = $request->query->get('entityId');

        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setAction('listParagraph');
        $generator->setEntityId($entityId);

        $type = $request->request->get('paragraph', null);
        if (!is_null($type)) {
            $repository = $this->getRepository();
            $entity     = $repository->find($entityId);

            $paragraph = new Paragraph();
            $paragraph->setEnable(true);
            $paragraph->setPosition(count($entity->getParagraphs()) + 1);
            $paragraph->setType($type);
            $entity->addParagraph($paragraph);

            $repository->save($entity);
        }

        $url = $generator->generateUrl();

        return $this->redirect($url);
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud->addFormTheme('admin/form.html.twig');

        return $crud;
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $queryBuilder = $this->filterListeTrash($searchDto, $queryBuilder);

        return $this->filterListRefUser($queryBuilder, $entityDto);
    }

    public function deleteParagraph(
        AdminContext $adminContext
    )
    {
        $request   = $adminContext->getRequest();
        $entityId  = $request->query->get('entityId');
        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setAction('listParagraph');

        $paragraphId = $request->request->get('paragraph', null);
        if (!is_null($paragraphId)) {
            $repository = $this->getRepositoryParagraph();
            $paragraph  = $repository->find($paragraphId);
            $repository->remove($paragraph);
            $repository->flush();
        }

        $generator->setEntityId($entityId);

        $url = $generator->generateUrl();

        return $this->redirect($url);
    }

    public function listParagraph(
        AdminContext $adminContext
    )
    {
        $entityId   = $adminContext->getRequest()->query->get('entityId');
        $repository = $this->getRepository();
        $entity     = $repository->find($entityId);
        $paragraphs = $entity->getParagraphs();

        return $this->render(
            'admin/pararaphs.html.twig',
            ['paragraphs' => $paragraphs]
        );
    }

    public function updateParagraph(
        AdminContext $adminContext
    )
    {
        $request   = $adminContext->getRequest();
        $generator = $this->container->get(AdminUrlGenerator::class);
        $entityId  = $request->query->get('entityId');
        $generator->setAction('listParagraph');
        $paragraphs = $request->request->get('paragraphs', null);
        if (!is_null($paragraphs)) {
            $paragraphs = explode(',', $paragraphs);
            $repository = $this->getRepositoryParagraph();
            foreach ($paragraphs as $position => $idParagraph) {
                $paragraph = $repository->find($idParagraph);
                $paragraph->setPosition($position + 1);
                $repository->save($paragraph);
            }
        }

        $generator->setEntityId($entityId);

        $url = $generator->generateUrl();

        return $this->redirect($url);
    }

    protected function addFieldBoolean()
    {
        $request      = $this->container->get('request_stack')->getCurrentRequest();
        $action       = $request->query->get('action', null);
        $booleanField = BooleanField::new('enable');
        $booleanField->renderAsSwitch(empty($action));

        return $booleanField;
    }

    protected function addFieldCategories(string $type)
    {
        $associationField = AssociationField::new('categories')->autocomplete();
        $associationField->setFormTypeOption('by_reference', false);
        $associationField->setQueryBuilder(
            function (QueryBuilder $queryBuilder) use ($type)
            {
                $queryBuilder->andWhere('entity.type = :type');
                $queryBuilder->setParameter('type', $type);
            }
        );

        return $associationField;
    }

    protected function addFieldID()
    {
        $idField = IdField::new('id');
        $idField->onlyOnDetail();

        return $idField;
    }

    protected function addFieldMetas(): array
    {
        return [
            FormField::addTab('SEO'),
            TextField::new('meta.title')->hideOnIndex(),
            TextField::new('meta.keywords')->hideOnIndex(),
            TextField::new('meta.description')->hideOnIndex(),
        ];
    }

    protected function addFieldParagraphs(string $pageName, string $form): array
    {
        // Disable $form because allow_add and allow_delete are not working for using multiple prototypes
        unset($form);

        $fields = [];
        if ('edit' !== $pageName) {
            $fields[] = ParagraphsField::new('paragraphs');

            return $fields;
        }

        $fields[] = FormField::addTab('Paragraphs')->hideWhenCreating();
        $fields[] = ParagraphsField::new('paragraphs')->hideWhenCreating();

        // $collectionField = CollectionField::new('paragraphs');
        // $collectionField->setEntryType($form);
        // $collectionField->setDefaultColumns('col-md-12 col-xxl-12');
        // $collectionField->setFormTypeOption(
        //     'entry_options', [
        //         'allow_add' => true,
        //         'allow_delete' => true
        //     ]
        // );
        // $collectionField->allowAdd();
        // $collectionField->allowDelete();

        // $fields[] = $collectionField;

        return $fields;
    }

    protected function addFieldRefUser()
    {
        $data  = [];
        $user  = $this->getUser();
        $roles = $user->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            return [];
        }

        $data[]           = FormField::addTab('refuser');
        $associationField = AssociationField::new('refuser');
        $associationField->autocomplete();
        $associationField->setSortProperty('username');

        $user  = $this->getUser();
        $roles = $user->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            $associationField->hideOnForm();
        }

        $data[] = $associationField;

        return $data;
    }

    protected function addFieldSlug()
    {
        $slugField = SlugField::new('slug');
        $slugField->hideOnIndex();
        $slugField->setFormTypeOptions(
            ['required' => false]
        );
        $slugField->setTargetFieldName('title');
        $slugField->setUnlockConfirmationMessage(
            'Attention, si vous changez le titre, le slug sera modifié'
        );

        return $slugField;
    }

    protected function addFieldTags(string $type)
    {
        $associationField = AssociationField::new('tags')->autocomplete();
        $associationField->setFormTypeOption('by_reference', false);
        $associationField->setQueryBuilder(
            function (QueryBuilder $queryBuilder) use ($type)
            {
                $queryBuilder->andWhere('entity.type = :type');
                $queryBuilder->setParameter('type', $type);
            }
        );

        return $associationField;
    }

    protected function addFieldTotalChild(string $type)
    {
        $collectionField = CollectionField::new($type);
        $collectionField->hideOnForm();
        $collectionField->formatValue(fn ($value) => count($value));

        return $collectionField;
    }

    protected function configureActionsBtn(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::INDEX);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->add(Crud::PAGE_NEW, Action::INDEX);
    }

    protected function configureActionsTrash(Actions $actions): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $this->configureActionsTrashBtn($request, $actions);
        $this->configureActionsTrashEmptyBtn($request, $actions);
        $this->configureActionsBtn($actions);
    }

    protected function configureActionsTrashBtn(Request $request, Actions $actions): void
    {
        $action = $request->query->get('action', null);
        if ('trash' == $action) {
            return;
        }

        $action    = Action::new('trash', 'Trash', 'fa fa-trash');
        $generator = $this->container->get(AdminUrlGenerator::class);
        $generator->setAction(Action::INDEX);
        $generator->setController(static::class);
        $generator->set('action', 'trash');

        $action->linkToUrl($generator->generateUrl());
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
    }

    protected function configureActionsTrashEmptyBtn(Request $request, Actions $actions): void
    {
        $action = $request->query->get('action', null);
        if (empty($action)) {
            return;
        }

        $action = Action::new('list', 'Liste', 'fa fa-list');
        $action->linkToCrudAction(Crud::PAGE_INDEX);
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('empty', 'Vider', 'fa fa-trash');
        $action->linkToRoute(
            'admin_empty',
            [
                'entity' => $this->getEntityFqcn(),
            ]
        );
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);

        $action = Action::new('restore', 'Restore');
        $action->linkToRoute(
            'admin_restore',
            static fn ($entity) => [
                'uuid'   => $entity->getId(),
                'entity' => $entity::class,
            ]
        );
        $actions->add(Crud::PAGE_INDEX, $action);
    }

    protected function getRepository(?string $entity = null)
    {
        $doctrine = $this->container->get('doctrine');

        $entity = is_null($entity) ? static::getEntityFqcn() : $entity;

        return $doctrine->getManagerForClass(static::getEntityFqcn())->getRepository($entity);
    }

    protected function getRepositoryParagraph(): ParagraphRepository
    {
        $doctrine = $this->container->get('doctrine');

        return $doctrine->getManagerForClass(Paragraph::class)->getRepository(Paragraph::class);
    }

    private function filterListeTrash(SearchDto $searchDto, QueryBuilder $queryBuilder): QueryBuilder
    {
        $request = $searchDto->getRequest();
        $action  = $request->query->get('action', null);
        if ('trash' == $action) {
            $queryBuilder->andWhere('entity.deletedAt IS NOT NULL');
        }

        return $queryBuilder;
    }

    private function filterListRefUser(QueryBuilder $queryBuilder, EntityDto $entityDto): QueryBuilder
    {
        $fqcn    = $entityDto->getFqcn();
        $entity  = new $fqcn();
        $methods = get_class_methods($entity);
        if (in_array('getRefuser', $methods)) {
            $user  = $this->getUser();
            $roles = $user->getRoles();
            if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
                $queryBuilder->andWhere('entity.refuser = :refuser');
                $queryBuilder->setParameter('refuser', $user);
            }
        }

        return $queryBuilder;
    }
}
