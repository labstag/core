<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Controller\Admin\Abstract\AbstractCrudControllerLib;
use Labstag\Entity\Meta;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Field\WysiwygField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PageCrudController extends AbstractCrudControllerLib
{
    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $this->setActionPublic($actions, 'admin_page_w3c', 'admin_page_public');
        $this->setEditDetail($actions);
        $this->configureActionsTrash($actions);

        return $actions;
    }

    #[\Override]
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

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $currentEntity = $this->getContext()->getEntity()->getInstance();
        yield $this->addTabPrincipal();
        $isSuperAdmin = $this->isSuperAdmin();
        $identity     = $this->getIdEntity($pageName, $currentEntity);

        foreach ($identity as $field) {
            yield $field;
        }

        $fieldChoice = $this->addFieldIsHome($currentEntity, $pageName);
        if ($fieldChoice instanceof ChoiceField) {
            yield $fieldChoice;
        }

        yield AssociationField::new('page', new TranslatableMessage('Page'));
        foreach ($this->crudFieldFactory->taxonomySet('page') as $field) {
            yield $field;
        }

        yield WysiwygField::new('resume', new TranslatableMessage('resume'))->hideOnIndex();
        $fieldsTabs = [
            $this->crudFieldFactory->paragraphFields($pageName),
            $this->crudFieldFactory->metaFields(),
            $this->crudFieldFactory->refUserFields($isSuperAdmin),
        ];
        foreach ($fieldsTabs as $fieldTab) {
            yield from $fieldTab;
        }

        yield $this->crudFieldFactory->workflowField();
        yield $this->crudFieldFactory->stateField();
        foreach ($this->crudFieldFactory->dateSet($pageName) as $field) {
            yield $field;
        }
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);

        $filters->add(EntityFilter::new('page', new TranslatableMessage('Page')));
        $this->crudFieldFactory->addFilterTags($filters, 'page');
        $this->crudFieldFactory->addFilterCategories($filters, 'page');

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Page
    {
        $page = new $entityFqcn();
        $this->workflowService->init($page);
        $meta = new Meta();
        $page->setMeta($meta);
        $home = $this->getRepository()->findOneBy(
            [
                'type' => PageEnum::HOME->value,
            ]
        );
        if ($home instanceof Page) {
            $page->setPage($home);
        }

        $page->setType(($home instanceof Page) ? PageEnum::PAGE->value : PageEnum::HOME->value);
        $page->setRefuser($this->getUser());

        return $page;
    }

    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    #[Route('/admin/page/{entity}/public', name: 'admin_page_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $page                       = $serviceEntityRepositoryLib->find($entity);

        return $this->publicLink($page);
    }

    #[Route('/admin/page/{entity}/w3c', name: 'admin_page_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $serviceEntityRepositoryLib = $this->getRepository();
        $page                       = $serviceEntityRepositoryLib->find($entity);

        return $this->linkw3CValidator($page);
    }

    protected function addFieldIsHome(?Page $page, string $pageName): ?ChoiceField
    {
        if ('new' === $pageName || ($page instanceof Page && PageEnum::HOME->value == $page->getType())) {
            return null;
        }

        $choiceField = ChoiceField::new('type', new TranslatableMessage('Type'));
        $data        = PageEnum::cases();
        $choices     = [];
        foreach ($data as $row) {
            $choices[$row->name] = $row->value;
        }

        $choiceField->setChoices($choices);
        $choiceField->setRequired(true);

        return $choiceField;
    }

    // Base identity set but slug possibly excluded depending on home type
    /**
     * @return mixed[]
     */
    private function getIdEntity(string $pageName, mixed $currentEntity): array
    {
        $identity = $this->crudFieldFactory->baseIdentitySet($pageName, self::getEntityFqcn());
        if ($currentEntity instanceof Page && PageEnum::HOME->value == $currentEntity->getType()) {
            // Remove slug field (present at index 2 if withSlug kept)
            unset($identity[2]);
        }

        return $identity;
    }
}
