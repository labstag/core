<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Entity\User;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;
use Symfony\Component\Translation\TranslatableMessage;

class ChapterCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions);
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield $this->addTabPrincipal();
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean('enable', new TranslatableMessage('Enable'));
        yield $this->addFieldTitle();
        yield $this->addFieldRefStory();
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('chapter');
        $fields = array_merge($this->addFieldParagraphs($pageName), $this->addFieldMetas());
        foreach ($fields as $field) {
            yield $field;
        }

        yield $this->addFieldWorkflow();
        yield $this->addFieldState();
        $date = $this->addTabDate();
        foreach ($date as $field) {
            yield $field;
        }
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->addFilterEnable($filters);
        $filters->add(EntityFilter::new('refstory', new TranslatableMessage('Story')));
        $this->addFilterTags($filters, 'chapter');

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn): Chapter
    {
        $chapter = new $entityFqcn();
        $this->workflowService->init($chapter);
        $meta = new Meta();
        $chapter->setMeta($meta);

        return $chapter;
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Chapter::class;
    }

    private function addFieldRefStory(): AssociationField
    {
        $associationField = AssociationField::new('refstory', new TranslatableMessage('Story'))->autocomplete();
        $user             = $this->getUser();
        $roles            = $user->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            /** @var User $user */
            $idUser = $user->getId();
            $associationField->setQueryBuilder(
                function (QueryBuilder $queryBuilder) use ($idUser): void
                {
                    $queryBuilder->leftjoin('entity.refuser', 'refuser');
                    $queryBuilder->andWhere('refuser.id = :id');
                    $queryBuilder->setParameter('id', $idUser);
                }
            );
        }

        $associationField->setSortProperty('title');

        return $associationField;
    }
}
