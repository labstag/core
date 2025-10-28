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
use Labstag\Entity\Story;
use Labstag\Entity\User;
use Labstag\Field\WysiwygField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class ChapterCrudController extends CrudControllerAbstract
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_chapter_w3c', 'admin_chapter_public');
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setEntityLabelInSingular(new TranslatableMessage('Chapter'));
        $crud->setEntityLabelInPlural(new TranslatableMessage('Chapters'));
        $crud->setDefaultSort(
            ['createdAt' => 'DESC']
        );

        return $crud;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal();
        $fields = [
            $this->crudFieldFactory->idField(),
            $this->crudFieldFactory->slugField(),
            $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
            $this->crudFieldFactory->titleField(),
            $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
            $this->addFieldRefStory(),
            $this->crudFieldFactory->tagsField('chapter'),
            WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex(),
        ];

        $this->crudFieldFactory->addFieldsToTab('principal', $fields);

        $this->crudFieldFactory->setTabParagraphs($pageName);

        $this->crudFieldFactory->setTabSEO();

        $this->crudFieldFactory->setTabWorkflow();
        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields();
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $filters->add(EntityFilter::new('refstory', new TranslatableMessage('Story')));
        $this->crudFieldFactory->addFilterTags($filters, 'chapter');

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Chapter
    {
        $chapter      = new $entityFqcn();
        $request      = $this->requestStack->getCurrentRequest();
        $defaultStory = $request->query->get('story');
        if ($defaultStory) {
            $repository = $this->getRepository(Story::class);
            $story      = $repository->find($defaultStory);
            $chapter->setRefstory($story);
        }

        $this->workflowService->init($chapter);
        $meta = new Meta();
        $chapter->setMeta($meta);

        return $chapter;
    }

    public static function getEntityFqcn(): string
    {
        return Chapter::class;
    }

    #[Route('/admin/chapter/{entity}/public', name: 'admin_chapter_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $chapter                         = $serviceEntityRepositoryAbstract->find($entity);

        return $this->publicLink($chapter);
    }

    #[Route('/admin/chapter/{entity}/w3c', name: 'admin_chapter_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryAbstract = $this->getRepository();
        $chapter                         = $serviceEntityRepositoryAbstract->find($entity);

        return $this->linkw3CValidator($chapter);
    }

    private function addFieldRefStory(): AssociationField
    {
        $associationField = AssociationField::new('refstory', new TranslatableMessage('Story'));
        $associationField->autocomplete();

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
