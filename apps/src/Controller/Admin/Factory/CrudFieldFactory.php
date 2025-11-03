<?php

namespace Labstag\Controller\Admin\Factory;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
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
use Labstag\Field\ParagraphsField;
use Labstag\Field\UploadFileField;
use Labstag\Field\UploadImageField;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\TagRepository;
use Labstag\Service\FileService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * Centralized factory for fields (EasyAdmin Fields) to reduce
 * duplication in CrudControllerAbstract.
 */
final class CrudFieldFactory
{

    private array $tabfields = [];

    public function __construct(
        #[AutowireIterator('labstag.datas')]
        private iterable $datas,
        #[AutowireIterator('labstag.shortcodes')]
        private iterable $shortcodes,
        private FileService $fileService,
    )
    {
    }

    public function addFieldIDShortcode(string $className): iterable
    {
        foreach ($this->datas as $data) {
            $shortcodes = $data->getShortCodes();
            if (!$data->supportsShortcode($className)) {
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

    public function addFilterCategories(Filters $filters, string $type): void
    {
        $filters->add(
            EntityFilter::new('categories', new TranslatableMessage('Categories'))->setFormTypeOption(
                'value_type_options.query_builder',
                static fn (CategoryRepository $categoryRepository): QueryBuilder => $categoryRepository->createQueryBuilder(
                    'c'
                )->andWhere('c.type = :type')->setParameter('type', $type)
            )
        );
    }

    public function addFilterEnable(Filters $filters): void
    {
        $filters->add(BooleanFilter::new('enable', new TranslatableMessage('Enable')));
    }

    public function addFilterRefUser(Filters $filters): void
    {
        $filters->add(EntityFilter::new('refuser', new TranslatableMessage('User')));
    }

    public function addFilterTags(Filters $filters, string $type): void
    {
        $filters->add(
            EntityFilter::new('tags', new TranslatableMessage('Tags'))->setFormTypeOption(
                'value_type_options.query_builder',
                static fn (TagRepository $tagRepository): QueryBuilder => $tagRepository->createQueryBuilder('t')->andWhere('t.type = :type')->setParameter('type', $type)
            )
        );
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

    public function categoriesField(string $type): AssociationField
    {
        $associationField = AssociationField::new('categories', new TranslatableMessage('Categories'));
        $associationField->autocomplete();
        $associationField->setTemplatePath('admin/field/categories.html.twig');
        $associationField->setFormTypeOption('by_reference', false);
        $associationField->setQueryBuilder(
            function (QueryBuilder $queryBuilder) use ($type): void {
                $queryBuilder->andWhere('entity.type = :type');
                $queryBuilder->setParameter('type', $type);
            }
        );

        return $associationField;
    }

    public function fileField(
        string $type,
        string $pageName,
        string $entityFqcn,
        ?string $label = null,
    ): TextField|UploadFileField
    {
        if ('edit' === $pageName || 'new' === $pageName) {
            return UploadFileField::new($type . 'File', $label ?? new TranslatableMessage('File'));
        }

        $this->fileService->getBasePath($entityFqcn, $type . 'File');

        return TextField::new($type, $label ?? new TranslatableMessage('File'));
    }

    public function getConfigureFields(): iterable
    {
        foreach ($this->tabfields as $tabfield) {
            if (0 === count($tabfield['fields'])) {
                continue;
            }

            if (1 !== count($this->tabfields)) {
                yield $tabfield['tab'];
            }

            yield from $tabfield['fields'];
        }
    }

    public function idField(): IdField
    {
        return IdField::new('id', new TranslatableMessage('ID'))->onlyOnDetail();
    }

    public function imageField(
        string $type,
        string $pageName,
        string $entityFqcn,
        ?string $label = null,
    ): ImageField|UploadImageField
    {
        if ('edit' === $pageName || 'new' === $pageName) {
            return UploadImageField::new($type . 'File', $label ?? new TranslatableMessage('Image'));
        }

        $basePath = $this->fileService->getBasePath($entityFqcn, $type . 'File');

        return ImageField::new($type, $label ?? new TranslatableMessage('Image'))->setBasePath($basePath);
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
        $this->addTab('other', FormField::addTab(new TranslatableMessage('Other'))->onlyOnIndex());
    }

    /**
     * @return array<int, FormField|ParagraphsField>
     */
    public function setTabParagraphs(string $pageName): void
    {
        if ('new' === $pageName) {
            return;
        }

        $this->addTab('paragraphs', FormField::addTab(new TranslatableMessage('Paragraphs')));
        $this->addFieldsToTab(
            'paragraphs',
            [ParagraphsField::new('paragraphs', new TranslatableMessage('Paragraphs'))->hideWhenCreating()]
        );
    }

    public function setTabPrincipal(string $entity): void
    {
        $this->addTab('principal', FormField::addTab(new TranslatableMessage('Principal')));
        $this->addFieldsToTab('principal', $this->addFieldIDShortcode($entity));
        $this->addFieldsToTab('principal', [$this->idField()]);
    }

    /**
     * @return array<int, FormField|TextField>
     */
    public function setTabSEO(): void
    {
        $this->addTab('seo', FormField::addTab(new TranslatableMessage('SEO')));
        $this->addFieldsToTab(
            'seo',
            [
                TextField::new('meta.title', new TranslatableMessage('Title'))->hideOnIndex(),
                TextField::new('meta.keywords', new TranslatableMessage('Keywords'))->hideOnIndex(),
                TextField::new('meta.description', new TranslatableMessage('Description'))->hideOnIndex(),
            ]
        );
    }

    /**
     * @return array<int, FormField|AssociationField>
     */
    public function setTabUser(bool $isSuperAdmin): void
    {
        if (!$isSuperAdmin) {
            return;
        }

        $this->addTab('user', FormField::addTab(new TranslatableMessage('User')));
        $associationField = AssociationField::new('refuser', new TranslatableMessage('User'));
        $associationField->setSortProperty('username');
        $this->addFieldsToTab('user', [$associationField]);
    }

    public function setTabWorkflow(): void
    {
        $this->addTab('workflows', FormField::addTab(new TranslatableMessage('Workflow'))->onlyOnIndex());

        $this->addFieldsToTab('workflows', [$this->workflowField(), $this->stateField()]);
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

    public function stateField(): TextField
    {
        return TextField::new('states', new TranslatableMessage('States'))->setTemplatePath(
            'admin/field/states.html.twig'
        )->onlyOnIndex();
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

    public function tagsField(string $type): AssociationField
    {
        $associationField = AssociationField::new('tags', new TranslatableMessage('Tags'));
        $associationField->autocomplete();
        $associationField->setTemplatePath('admin/field/tags.html.twig');
        $associationField->setFormTypeOption('by_reference', false);
        $associationField->setQueryBuilder(
            function (QueryBuilder $queryBuilder) use ($type): void {
                $queryBuilder->andWhere('entity.type = :type');
                $queryBuilder->setParameter('type', $type);
            }
        );

        return $associationField;
    }

    /**
     * Helper returning taxonomy related fields (tags + categories) for a given type.
     *
     * @return array<int, AssociationField>
     */
    public function taxonomySet(string $type): array
    {
        return [
            $this->tagsField($type),
            $this->categoriesField($type),
        ];
    }

    public function titleField(): TextField
    {
        return TextField::new('title', new TranslatableMessage('Title'));
    }

    public function totalChildField(string $type): CollectionField
    {
        return CollectionField::new($type, new TranslatableMessage('Childs'))->hideOnForm()->formatValue(
            fn ($value): int => is_countable($value) ? count($value) : 0
        );
    }

    public function workflowField(): TextField
    {
        return TextField::new('workflow', new TranslatableMessage('Workflow'))->setTemplatePath(
            'admin/field/workflow.html.twig'
        )->onlyOnIndex();
    }
}
