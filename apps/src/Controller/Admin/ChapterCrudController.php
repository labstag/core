<?php

namespace Labstag\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Chapter;
use Labstag\Entity\Story;
use Labstag\Entity\User;
use Labstag\Field\WysiwygField;
use Labstag\Message\StoryMessage;
use Override;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class ChapterCrudController extends CrudControllerAbstract
{
    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->actionsFactory->init($actions, self::getEntityFqcn(), static::class);
        $this->setUpdateAction();

        return $this->actionsFactory->show();
    }

    #[Override]
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

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        $this->crudFieldFactory->setTabPrincipal($this->getContext());
        $fields = [
            $this->crudFieldFactory->slugField(readOnly: true),
            $this->crudFieldFactory->booleanField('enable', new TranslatableMessage('Enable')),
            $this->crudFieldFactory->titleField(),
            $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
            $this->addFieldRefStory(),
            WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex(),
        ];

        $this->crudFieldFactory->addFieldsToTab('principal', $fields);

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterEnable($filters);
        $filters->add(EntityFilter::new('refstory', new TranslatableMessage('Story')));
        $this->crudFieldFactory->addFilterTagsFor($filters, self::getEntityFqcn());

        return $filters;
    }

    #[Override]
    public function createEntity(string $entityFqcn): Chapter
    {
        $chapter      = parent::createEntity($entityFqcn);
        $request      = $this->requestStack->getCurrentRequest();
        $defaultStory = $request->query->get('story');
        if ($defaultStory) {
            $story      = $this->getRepository(Story::class)->find($defaultStory);
            $chapter->setRefstory($story);
        }

        return $chapter;
    }

    public static function getEntityFqcn(): string
    {
        return Chapter::class;
    }

    public function updateChapter(Request $request): RedirectResponse
    {
        $entityId                        = $request->query->get('entityId');
        $repositoryAbstract              = $this->getRepository();
        $chapter                         = $repositoryAbstract->find($entityId);
        $this->messageBus->dispatch(new StoryMessage($chapter->getRefstory()->getId()));
        if ($request->headers->has('referer')) {
            $url = $request->headers->get('referer');
            if (is_string($url) && '' !== $url) {
                return $this->redirect($url);
            }
        }

        return $this->redirectToRoute('admin_story_index');
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

    private function setUpdateAction(): void
    {
        if (!$this->actionsFactory->isTrash()) {
            return;
        }

        $action = Action::new('updateChapter', new TranslatableMessage('Update'));
        $action->linkToCrudAction('updateChapter');
        $action->displayIf(static fn ($entity): bool => is_null($entity->getDeletedAt()));

        $this->actionsFactory->add(Crud::PAGE_DETAIL, $action);
        $this->actionsFactory->add(Crud::PAGE_EDIT, $action);
        $this->actionsFactory->add(Crud::PAGE_INDEX, $action);
    }
}
