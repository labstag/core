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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Labstag\Entity\Paragraph;
use Labstag\Field\ParagraphsField;
use Labstag\Repository\ParagraphRepository;
use Labstag\Repository\TagRepository;
use Labstag\Service\BlockService;
use Labstag\Service\FileService;
use Labstag\Service\ParagraphService;
use Labstag\Service\SiteService;
use Labstag\Service\UserService;
use Labstag\Service\WorkflowService;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Vich\UploaderBundle\Form\Type\VichImageType;

abstract class AbstractCrudControllerLib extends AbstractCrudController
{
    public function __construct(
        protected TagRepository $tagRepository,
        protected FileService $fileService,
        protected SiteService $siteService,
        protected BlockService $blockService,
        protected ParagraphService $paragraphService,
        protected WorkflowService $workflowService,
        protected RequestStack $requestStack,
        protected UserService $userService
    )
    {
    }

    public function addFieldTitle()
    {
        return TextField::new('title', new TranslatableMessage('Title'));
    }

    public function addFilterRefUser($filters)
    {
        $filters->add(EntityFilter::new('refuser', new TranslatableMessage('Refuser')));
    }

    public function addFilterEnable($filters)
    {
        $filters->add(BooleanFilter::new('enable', new TranslatableMessage('Enable')));
    }

    public function addCreatedAtField()
    {
        return DateTimeField::new('createdAt')->hideOnForm();
    }

    public function addUpdatedAtField()
    {
        return DateTimeField::new('updatedAt')->hideOnForm();
    }

    public function addTabPrincipal()
    {
        return FormField::addTab(new TranslatableMessage('Principal'));
    }

    public function addFieldImageUpload(string $type, string $pageName, ?string $label = null)
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            $imageField = TextField::new($type.'File', is_null($label) ? new TranslatableMessage('Image') : $label);
            $imageField->setFormType(VichImageType::class);

            return $imageField;
        }

        $entity     = $this->getEntityFqcn();
        $basePath   = $this->fileService->getBasePath($entity, $type.'File');
        $imageField = ImageField::new($type, is_null($label) ? new TranslatableMessage('Image') : $label);
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

            $this->paragraphService->addParagraph($entity, $type);
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

    public function linkPublic(AdminContext $adminContext)
    {
        $entity = $adminContext->getEntity()->getInstance();
        $slug   = $this->siteService->getSlugByEntity($entity);

        return $this->redirect(
            $this->generateUrl(
                'front',
                ['slug' => $slug]
            )
        );
    }

    public function linkw3CValidator(AdminContext $adminContext)
    {
        $entity = $adminContext->getEntity()->getInstance();
        $slug   = $this->siteService->getSlugByEntity($entity);

        return $this->redirect(
            'https://validator.w3.org/nu/?doc='.$this->generateUrl(
                'front',
                ['slug' => $slug],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
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
        $booleanField = BooleanField::new('enable', new TranslatableMessage('Enable'));
        $booleanField->renderAsSwitch(empty($action));

        return $booleanField;
    }

    protected function addFieldCategories(string $type)
    {
        $associationField = AssociationField::new('categories', new TranslatableMessage('Categories'))->autocomplete();
        $associationField->setTemplatePath('admin/field/categories.html.twig');
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
        $idField = IdField::new('id', new TranslatableMessage('ID'));
        $idField->onlyOnDetail();

        return $idField;
    }

    protected function addFieldMetas(): array
    {
        return [
            FormField::addTab(new TranslatableMessage('SEO')),
            TextField::new('meta.title', new TranslatableMessage('Title'))->hideOnIndex(),
            TextField::new('meta.keywords', new TranslatableMessage('Keywords'))->hideOnIndex(),
            TextField::new('meta.description', new TranslatableMessage('Description'))->hideOnIndex(),
        ];
    }

    protected function addFieldParagraphs(string $pageName, string $form): array
    {
        // Disable $form because allow_add and allow_delete are not working for using multiple prototypes
        unset($form);

        $fields = [];
        if ('new' === $pageName) {
            return $fields;
        }

        if ('edit' !== $pageName) {
            $fields[] = ParagraphsField::new('paragraphs', new TranslatableMessage('Paragraphs'));

            return $fields;
        }

        $fields[] = FormField::addTab(new TranslatableMessage('Paragraphs'))->hideWhenCreating();
        $fields[] = ParagraphsField::new('paragraphs', new TranslatableMessage('Paragraphs'))->hideWhenCreating();

        // $collectionField = CollectionField::new('paragraphs', new TranslatableMessage('Paragraphs'));
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

        $data[]           = FormField::addTab(new TranslatableMessage('refuser'));
        $associationField = AssociationField::new('refuser', new TranslatableMessage('Refuser'));
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
        $slugField = SlugField::new('slug', new TranslatableMessage('Slug'));
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
        $associationField = AssociationField::new('tags', new TranslatableMessage('Tags'))->autocomplete();
        $associationField->setTemplatePath('admin/field/tags.html.twig');
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

        $action    = Action::new('trash', new TranslatableMessage('Trash'), 'fa fa-trash');
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

        $action = Action::new('list', new TranslatableMessage('List'), 'fa fa-list');
        $action->linkToCrudAction(Crud::PAGE_INDEX);
        $action->createAsGlobalAction();

        $actions->add(Crud::PAGE_INDEX, $action);

        $action = Action::new('empty', new TranslatableMessage('Empty'), 'fa fa-trash');
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

        $action = Action::new('restore', new TranslatableMessage('Restore'));
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

    protected function setActionPublic(Actions $actions): void
    {
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        $action = $this->setLinkPublicAction();
        $actions->add(Crud::PAGE_DETAIL, $action);
        $actions->add(Crud::PAGE_EDIT, $action);
        $actions->add(Crud::PAGE_INDEX, $action);

        $w3caction = $this->setW3cValidatorAction();
        $actions->add(Crud::PAGE_EDIT, $w3caction);
        $actions->add(Crud::PAGE_INDEX, $w3caction);
        $actions->add(Crud::PAGE_DETAIL, $w3caction);
    }

    protected function setEditDetail(Actions $actions): void
    {
        $actions->add(Crud::PAGE_EDIT, Action::DETAIL);
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

    private function setLinkPublicAction()
    {
        $action = Action::new('linkPublic', new TranslatableMessage('View Page'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('linkPublic');
        $action->displayIf(static fn ($entity) => is_null($entity->getDeletedAt()));

        return $action;
    }

    private function setW3cValidatorAction()
    {
        $action = Action::new('linkw3CValidator', new TranslatableMessage('W3C Validator'));
        $action->setHtmlAttributes(
            ['target' => '_blank']
        );
        $action->linkToCrudAction('linkw3CValidator');
        $action->displayIf(static fn ($entity) => is_null($entity->getDeletedAt()));

        return $action;
    }
}
