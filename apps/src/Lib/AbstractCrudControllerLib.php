<?php

namespace Labstag\Lib;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
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
use Labstag\Repository\TagRepository;
use Labstag\Service\VichImageFieldService;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

abstract class AbstractCrudControllerLib extends AbstractCrudController
{
    public function __construct(
        protected TagRepository $tagRepository,
        protected VichImageFieldService $vichImageFieldService,
        protected UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    public function addFieldImageUpload(string $type, string $pageName)
    {
        if ($pageName == Crud::PAGE_EDIT) {
            $imageField = TextField::new($type.'File');
            $imageField->setFormType(VichImageType::class);

            return $imageField;
        }
        
        $entity = $this->getEntityFqcn();
        $basePath = $this->vichImageFieldService->getBasePath($entity, $type.'File');
        $imageField = ImageField::new($type);
        $imageField->setBasePath($basePath);

        return $imageField;
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

    protected function addFieldMetas()
    {
        return [
            FormField::addTab('SEO'),
            TextField::new('meta.title')->hideOnIndex(),
            TextField::new('meta.keywords')->hideOnIndex(),
            TextField::new('meta.description')->hideOnIndex(),
        ];
    }

    protected function addFieldRefUser()
    {
        $associationField = AssociationField::new('refuser');
        $associationField->autocomplete();
        $associationField->setSortProperty('username');

        $user  = $this->getUser();
        $roles = $user->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            $associationField->hideOnForm();
        }

        return $associationField;
    }

    protected function addFieldSlug()
    {
        $slugField = SlugField::new('slug');
        $slugField->hideOnIndex();
        $slugField->setFormTypeOptions(
            [
                'required' => false
            ]
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
