<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Labstag\Entity\Chapter;
use Labstag\Entity\Meta;
use Labstag\Form\Paragraphs\ChapterType;
use Labstag\Lib\AbstractCrudControllerLib;
use Override;

class ChapterCrudController extends AbstractCrudControllerLib
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Principal');
        yield $this->addFieldID();
        yield $this->addFieldSlug();
        yield $this->addFieldBoolean();
        yield TextField::new('title');
        $associationField = AssociationField::new('refhistory')->autocomplete();
        $user             = $this->getUser();
        $roles            = $user->getRoles();
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            $idUser = $user->getId();
            $associationField->setQueryBuilder(
                function (QueryBuilder $queryBuilder) use ($idUser)
                {
                    $queryBuilder->leftjoin('entity.refuser', 'refuser');
                    $queryBuilder->andWhere('refuser.id = :id');
                    $queryBuilder->setParameter('id', $idUser);
                }
            );
        }

        $associationField->setSortProperty('title');
        yield $associationField;
        yield $this->addFieldImageUpload('img', $pageName);
        yield $this->addFieldTags('chapter');
        $fields = array_merge(
            $this->addFieldParagraphs($pageName, ChapterType::class),
            $this->addFieldMetas()
        );
        foreach ($fields as $field) {
            yield $field;
        }
    }

    #[Override]
    public function createEntity(string $entityFqcn)
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
}
