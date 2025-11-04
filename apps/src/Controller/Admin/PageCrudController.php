<?php

namespace Labstag\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Labstag\Field\WysiwygField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class PageCrudController extends CrudControllerAbstract
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
        $this->crudFieldFactory->setTabPrincipal(self::getEntityFqcn());
        $this->crudFieldFactory->addFieldsToTab('principal', $this->getIdEntity($pageName, $currentEntity));

        $fieldChoice  = $this->addFieldIsHome($currentEntity, $pageName);
        $wysiwygField = WysiwygField::new('resume', new TranslatableMessage('resume'));
        $wysiwygField->hideOnIndex();
        if ($fieldChoice instanceof ChoiceField) {
            $this->crudFieldFactory->addFieldsToTab('principal', [$fieldChoice, $wysiwygField]);
        }

        $this->crudFieldFactory->addFieldsToTab(
            'principal',
            [AssociationField::new('page', new TranslatableMessage('Page'))]
        );
        $this->crudFieldFactory->addFieldsToTab('principal', $this->crudFieldFactory->taxonomySet());

        $this->crudFieldFactory->setTabParagraphs($pageName);

        $this->crudFieldFactory->setTabSEO();

        $this->crudFieldFactory->setTabUser($this->isSuperAdmin());

        $this->crudFieldFactory->setTabWorkflow();

        $this->crudFieldFactory->setTabDate($pageName);

        yield from $this->crudFieldFactory->getConfigureFields($pageName);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $this->crudFieldFactory->addFilterRefUser($filters);
        $this->crudFieldFactory->addFilterEnable($filters);

        $filters->add(EntityFilter::new('page', new TranslatableMessage('Page')));
        $this->crudFieldFactory->addFilterTags($filters);
        $this->crudFieldFactory->addFilterCategories($filters);

        return $filters;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Page
    {
        $page = parent::createEntity($entityFqcn);
        $home = $this->getRepository()->findOneBy(
            [
                'type' => PageEnum::HOME->value,
            ]
        );
        if ($home instanceof Page) {
            $page->setPage($home);
        }

        $page->setType(($home instanceof Page) ? PageEnum::PAGE->value : PageEnum::HOME->value);

        return $page;
    }

    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    #[Route('/admin/page/{entity}/public', name: 'admin_page_public')]
    public function linkPublic(string $entity): RedirectResponse
    {
        $RepositoryAbstract              = $this->getRepository();
        $page                            = $RepositoryAbstract->find($entity);

        return $this->publicLink($page);
    }

    #[Route('/admin/page/{entity}/w3c', name: 'admin_page_w3c')]
    public function w3c(string $entity): RedirectResponse
    {
        $RepositoryAbstract              = $this->getRepository();
        $page                            = $RepositoryAbstract->find($entity);

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
        $fields   = [
            $this->crudFieldFactory->slugField(),
            $this->crudFieldFactory->booleanField('enable', (string) new TranslatableMessage('Enable')),
            $this->crudFieldFactory->titleField(),
            $this->crudFieldFactory->imageField('img', $pageName, self::getEntityFqcn()),
        ];
        if ($currentEntity instanceof Page && PageEnum::HOME->value == $currentEntity->getType()) {
            // Remove slug field (present at index 2 if withSlug kept)
            unset($fields[0]);
        }

        return $fields;
    }
}
