<?php

namespace Labstag\Controller\Admin\Factory;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
use Labstag\Entity\EntityWithParagraphsInterface;
use Labstag\Entity\User;
use Labstag\Field\ParagraphsField;
use Labstag\Field\UploadFileField;
use Labstag\Field\UploadImageField;
use Labstag\Service\FileService;
use Labstag\Service\WorkflowService;
use ReflectionClass;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Centralized factory for fields (EasyAdmin Fields) to reduce
 * duplication in CrudControllerAbstract.
 */
final class CrudFieldFactory
{

    private ?AdminContext $adminContext = null;

    private array $tabfields = [];

    public function __construct(
        #[AutowireIterator('labstag.datas')]
        private iterable $datas,
        #[AutowireIterator('labstag.shortcodes')]
        private iterable $shortcodes,
        private FileService $fileService,
        private Security $security,
        private ManagerRegistry $managerRegistry,
        private WorkflowService $workflowService,
    )
    {
    }

    public function addFieldIDShortcode(): iterable
    {
        $fqcn = $this->getFqcn();
        foreach ($this->datas as $data) {
            $shortcodes = $data->getShortCodes();
            if (!$data->supportsShortcode($fqcn)) {
                continue;
            }

            if (0 === count($shortcodes)) {
                continue;
            }

            yield from $this->shortcodeField($shortcodes);

            return;
        }
    }

    public function addFieldsToTab(string $tabName, $fields): void
    {
        if (!isset($this->tabfields[$tabName])) {
            throw new RuntimeException(
                sprintf(
                    'Tab "%s" not found in CrudFieldFactory. Please add it first using addTab().',
                    $tabName
                )
            );
        }

        foreach ($fields as $field) {
            $this->tabfields[$tabName]['fields'][] = $field;
        }
    }

    public function addFilterCategories(Filters $filters): void
    {
        $entityFilter = EntityFilter::new('categories', new TranslatableMessage('Categories'));
        $filters->add($entityFilter);
    }

    /**
     * Add categories filter only if the given entity actually has a Doctrine association named 'categories'.
     */
    public function addFilterCategoriesFor(Filters $filters, string $entityFqcn): void
    {
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;
        if (!$metadata instanceof ClassMetadata || !$metadata->hasAssociation('categories')) {
            return;
        }

        $this->addFilterCategories($filters);
    }

    public function addFilterEnable(Filters $filters): void
    {
        $filters->add(BooleanFilter::new('enable', new TranslatableMessage('Enable')));
    }

    /**
     * Add refuser filter only if the given entity actually has a Doctrine association named 'refuser'.
     */
    public function addFilterRefUserFor(Filters $filters, string $entityFqcn): void
    {
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;
        if (!$metadata instanceof ClassMetadata || !$metadata->hasAssociation('refuser')) {
            return;
        }

        $filters->add(EntityFilter::new('refuser', new TranslatableMessage('User')));
    }

    /**
     * Add categories filter only if the given entity actually has a Doctrine association named 'categories'.
     */
    public function addFilterTagsFor(Filters $filters, string $entityFqcn): void
    {
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;
        if (!$metadata instanceof ClassMetadata || !$metadata->hasAssociation('tags')) {
            return;
        }

        $entityFilter = EntityFilter::new('tags', new TranslatableMessage('Tags'));
        $filters->add($entityFilter);
    }

    public function addTab($tabName, FormField $formField): void
    {
        if (isset($this->tabfields[$tabName])) {
            return;
        }

        $this->tabfields[$tabName] = [
            'tab'    => $formField,
            'fields' => [],
        ];
    }

    public function booleanField(string $propertyName, string $label, bool $asSwitch = true): BooleanField
    {
        $booleanField = BooleanField::new($propertyName, $label);
        if ($asSwitch) {
            $booleanField->renderAsSwitch(true);
        }

        return $booleanField;
    }

    public function categoriesField(): AssociationField
    {
        $associationField = AssociationField::new('categories', new TranslatableMessage('Categories'));
        $associationField->setTemplatePath('admin/field/categories.html.twig');

        return $associationField;
    }

    /**
     * Page-aware variant to avoid AssociationConfigurator errors on index/detail pages.
     * - On index/detail: always return a read-only CollectionField (count/list via template).
     * - On edit/new: only return an AssociationField if Doctrine metadata confirms the association,
     *   otherwise hide the field on forms (no-op for safety).
     */
    public function categoriesFieldForPage(string $entityFqcn, string $pageName): AssociationField
    {
        $associationField = $this->categoriesField();
        // Always safe on listing/detail pages: no AssociationField to configure
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL], true)
        ) {
            $associationField->onlyOnDetail();

            return $associationField;
        }

        // For edit/new pages, check the real Doctrine association
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;

        if ($metadata instanceof ClassMetadata && $metadata->hasAssociation('categories')) {
            $associationField->autocomplete();
            $associationField->setFormTypeOption('by_reference', false);

            return $associationField;
        }

        // No association: ensure nothing is rendered on the form
        $associationField->hideOnForm();

        return $associationField;
    }

    public function correctionFieldsTab(array $tabfields, string $pageName): array
    {
        $corrected = [];
        foreach ($tabfields as $key => $tabfield) {
            $tabfield['fields'] = array_filter(
                $tabfield['fields'],
                fn ($field): bool => $this->isFieldVisibleOnPage($field, $pageName)
            );
            if ([] === $tabfield['fields']) {
                continue;
            }

            $corrected[$key] = $tabfield;
        }

        return $corrected;
    }

    public function fileField(
        string $type,
        string $pageName,
        string $entityFqcn,
        ?string $label = null,
    ): TextField|UploadFileField
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            return UploadFileField::new($type . 'File', $label ?? new TranslatableMessage('File'));
        }

        $this->fileService->getBasePath($entityFqcn, $type . 'File');

        return TextField::new($type, $label ?? new TranslatableMessage('File'));
    }

    public function getConfigureFields(string $pageName): iterable
    {
        $this->setTabParagraphs($pageName);
        $this->setTabSEO();
        $this->setTabWorkflow();
        $this->setTabUser();
        $tabfields = $this->correctionFieldsTab($this->tabfields, $pageName);
        foreach ($tabfields as $tabfield) {
            if (1 !== count($this->tabfields)) {
                yield $tabfield['tab'];
            }

            yield from $tabfield['fields'];
        }
    }

    public function idField(): IdField
    {
        $idField = IdField::new('id', new TranslatableMessage('ID'));
        $idField->onlyOnDetail();

        return $idField;
    }

    public function imageField(
        string $type,
        string $pageName,
        string $entityFqcn,
        ?string $label = null,
    ): ImageField|UploadImageField
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            return UploadImageField::new($type . 'File', $label ?? new TranslatableMessage('Image'));
        }

        $basePath = $this->fileService->getBasePath($entityFqcn, $type . 'File');

        $imageField = ImageField::new($type, $label ?? new TranslatableMessage('Image'));
        $imageField->setBasePath($basePath);

        return $imageField;
    }

    public function setTabConfig(): void
    {
        $this->addTab('config', FormField::addTab(new TranslatableMessage('Config')));
    }

    /**
     * Date tab helper (tab + createdAt + updatedAt).
     *
     * @return array<int, mixed>
     */
    public function setTabDate(string $pageName): void
    {
        if ('new' === $pageName) {
            return;
        }

        $this->addTab('date', FormField::addTab(new TranslatableMessage('Date')));
        $dateTimeField = DateTimeField::new('createdAt', new TranslatableMessage('Created At'));
        $dateTimeField->hideWhenCreating();

        $updatedAtField = DateTimeField::new('updatedAt', new TranslatableMessage('updated At'));
        $updatedAtField->hideWhenCreating();
        $updatedAtField->hideOnIndex();
        $this->addFieldsToTab('date', [$dateTimeField, $updatedAtField]);
    }

    public function setTabOther(): void
    {
        $this->addTab('other', FormField::addTab(new TranslatableMessage('Other')));
    }

    public function setTabPrincipal(AdminContext $adminContext): void
    {
        $this->adminContext = $adminContext;
        $this->addTab('principal', FormField::addTab(new TranslatableMessage('Principal')));

        $this->addFieldsToTab('principal', $this->addFieldIDShortcode());
        $this->addFieldsToTab('principal', [$this->idField()]);
    }

    public function shortcodeField(array $shortcodes): iterable
    {
        foreach ($this->shortcodes as $shortcode) {
            if (!in_array($shortcode::class, $shortcodes)) {
                continue;
            }

            $textField = TextField::new('id', new TranslatableMessage('Shortcode'));
            $textField->formatValue(fn ($identity): string => $shortcode->generate($identity));
            $textField->onlyOnDetail();

            yield $textField;
        }

        return [];
    }

    public function slugField($readOnly = false, ?string $target = 'title'): SlugField
    {
        $slugField = SlugField::new('slug', new TranslatableMessage('Slug'));
        $slugField->hideOnIndex();
        $slugField->setFormTypeOptions(
            ['required' => false]
        );
        $slugField->setTargetFieldName($target);
        $slugField->setUnlockConfirmationMessage(
            new TranslatableMessage('Are you sure you want to edit the slug manually?')
        );
        if ($readOnly) {
            $slugField->hideOnForm();
        }

        return $slugField;
    }

    public function stateField(): CollectionField
    {
        $collectionField = CollectionField::new('states', new TranslatableMessage('States'));
        $collectionField->setTemplatePath('admin/field/states.html.twig');
        $collectionField->onlyOnIndex();

        return $collectionField;
    }

    /**
     * Helper group for TAC boolean configuration fields (Configuration entity).
     *
     * @param array<string,string> $names label translation raw strings
     *
     * @return array<int, BooleanField>
     */
    public function tacBooleanSet(array $names): array
    {
        $fields = [];
        foreach ($names as $property => $label) {
            $fields[] = $this->booleanField($property, $label);
        }

        return $fields;
    }

    public function tagsField(): AssociationField
    {
        $associationField = AssociationField::new('tags', new TranslatableMessage('Tags'));
        $associationField->setTemplatePath('admin/field/tags.html.twig');

        return $associationField;
    }

    /**
     * Page-aware variant to avoid AssociationConfigurator errors on index/detail pages.
     * - On index/detail: always return a read-only CollectionField (count/list via template).
     * - On edit/new: only return an AssociationField if Doctrine metadata confirms the association,
     *   otherwise hide the field on forms (no-op for safety).
     */
    public function tagsFieldForPage(string $entityFqcn, string $pageName): AssociationField
    {
        $associationField = $this->tagsField();
        // Always safe on listing/detail pages: no AssociationField to configure
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL], true)
        ) {
            $associationField->onlyOnDetail();

            return $associationField;
        }

        // For edit/new pages, check the real Doctrine association
        $entityManager       = $this->managerRegistry->getManagerForClass($entityFqcn);
        $metadata            = $entityManager instanceof ObjectManager ? $entityManager->getClassMetadata(
            $entityFqcn
        ) : null;

        if ($metadata instanceof ClassMetadata && $metadata->hasAssociation('tags')) {
            $associationField->autocomplete();
            $associationField->setFormTypeOption('by_reference', false);

            return $associationField;
        }

        $associationField->hideOnForm();

        return $associationField;
    }

    /**
     * Helper returning taxonomy related fields (tags + categories) for a given type.
     *
     * @return array<int, AssociationField>
     */
    public function taxonomySet(string $entityFqcn, string $pageName): array
    {
        return [
            $this->tagsFieldForPage($entityFqcn, $pageName),
            $this->categoriesFieldForPage($entityFqcn, $pageName),
        ];
    }

    public function titleField(): TextField
    {
        return TextField::new('title', new TranslatableMessage('Title'));
    }

    public function totalChildField(string $type): CollectionField
    {
        $collectionField = CollectionField::new($type, new TranslatableMessage('Childs'));
        $collectionField->hideOnForm();
        $collectionField->formatValue(fn ($value): int => is_countable($value) ? count($value) : 0);

        return $collectionField;
    }

    public function workflowField(): CollectionField
    {
        $collectionField = CollectionField::new('workflow', new TranslatableMessage('Workflow'));
        $collectionField->setTemplatePath('admin/field/workflow.html.twig');
        $collectionField->onlyOnIndex();

        return $collectionField;
    }

    private function getFqcn(): ?string
    {
        $entityDto = $this->adminContext->getEntity();
        if (is_null($entityDto)) {
            return null;
        }

        return $entityDto->getFqcn();
    }

    private function getInstance()
    {
        $entityDto = $this->adminContext->getEntity();
        if (is_null($entityDto)) {
            return null;
        }

        return $entityDto->getInstance();
    }

    private function isFieldVisibleOnPage($field, string $pageName): bool
    {
        $dto = $field->getAsDto();

        return match ($pageName) {
            Crud::PAGE_INDEX  => $dto->isDisplayedOn(Crud::PAGE_INDEX),
            Crud::PAGE_DETAIL => $dto->isDisplayedOn(Crud::PAGE_DETAIL),
            Crud::PAGE_EDIT   => $dto->isDisplayedOn(Crud::PAGE_EDIT),
            Crud::PAGE_NEW    => $dto->isDisplayedOn(Crud::PAGE_NEW),
            default           => true,
        };
    }

    private function isSuperAdmin(): bool
    {
        $user = $this->security->getUser();
        if (!is_object($user)) {
            return false;
        }

        return in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
    }

    /**
     * @return array<int, FormField|ParagraphsField>
     */
    private function setTabParagraphs(string $pageName): void
    {
        $instance = $this->getInstance();
        if (Crud::PAGE_NEW === $pageName || null == $instance) {
            return;
        }

        $reflectionClass = new ReflectionClass($instance);
        if (!$reflectionClass->implementsInterface(EntityWithParagraphsInterface::class)) {
            return;
        }

        $key = 'paragraphs';
        $this->addTab($key, FormField::addTab(new TranslatableMessage('Paragraphs')));
        $paragraphsField = ParagraphsField::new('paragraphs', new TranslatableMessage('Paragraphs'));
        $paragraphsField->hideWhenCreating();
        $paragraphsField->hideOnIndex();
        $this->addFieldsToTab($key, [$paragraphsField]);
    }

    /**
     * @return array<int, FormField|TextField>
     */
    private function setTabSEO(): void
    {
        $instance = $this->getInstance();
        if (is_null($instance)) {
            return;
        }

        $reflectionClass = new ReflectionClass($instance);
        if (!$reflectionClass->hasMethod('getMeta')) {
            return;
        }

        $this->addTab('seo', FormField::addTab(new TranslatableMessage('SEO')));
        $textField = TextField::new('meta.title', new TranslatableMessage('Title'));
        $textField->hideOnIndex();

        $keywords = TextField::new('meta.keywords', new TranslatableMessage('Keywords'));
        $keywords->hideOnIndex();

        $description = TextField::new('meta.description', new TranslatableMessage('Description'));
        $description->hideOnIndex();
        $this->addFieldsToTab('seo', [$textField, $keywords, $description]);
    }

    /**
     * @return array<int, FormField|AssociationField>
     */
    private function setTabUser(): void
    {
        if (!$this->isSuperAdmin()) {
            return;
        }

        $fqcn            = $this->getFqcn();
        $reflectionClass = new ReflectionClass($fqcn);
        // if ($reflectionClass->isAbstract() || !$isSuperAdmin || !$reflectionClass->hasMethod('getRefuser')) {
        if ($reflectionClass->isAbstract() || !$reflectionClass->hasMethod('getRefuser')) {
            return;
        }

        $objectRepository = $this->managerRegistry->getRepository(User::class);
        $users            = $objectRepository->findAll();
        if (1 === count($users)) {
            return;
        }

        $this->addTab('user', FormField::addTab(new TranslatableMessage('User')));
        $associationField = AssociationField::new('refuser', new TranslatableMessage('User'));
        $associationField->setSortProperty('username');
        $this->addFieldsToTab('user', [$associationField]);
    }

    private function setTabWorkflow(): void
    {
        $fqcn            = $this->getFqcn();
        $reflectionClass = new ReflectionClass($fqcn);
        if ($reflectionClass->isAbstract()) {
            return;
        }

        $entity = new $fqcn();
        if (!$this->workflowService->has($entity)) {
            return;
        }

        $this->addTab('workflows', FormField::addTab(new TranslatableMessage('Workflow')));

        $this->addFieldsToTab('workflows', [$this->workflowField(), $this->stateField()]);
    }
}
