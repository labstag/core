<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Notification;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class NotificationCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->actionsFactory->setReadOnly(true);

        return $this->actionsFactory->show();
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Page'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Pages'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', new TranslatableMessage('Title'));
        yield TextEditorField::new('message', new TranslatableMessage('Message'));
        yield DateTimeField::new('createdAt', new TranslatableMessage('Created At'));
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fieldCollection,
        FilterCollection $filterCollection,
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fieldCollection, $filterCollection);
        $user         = $this->getUser();
        $queryBuilder->andWhere('entity.refuser = :user');
        $queryBuilder->setParameter('user', $user->getId());

        return $queryBuilder;
    }

    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }
}
