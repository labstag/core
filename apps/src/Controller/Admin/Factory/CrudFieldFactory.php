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
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\TagRepository;
use Labstag\Service\FileService;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Centralized factory for fields (EasyAdmin Fields) to reduce
 * duplication in CrudControllerAbstract.
 */
final class CrudFieldFactory
{
    public function __construct(
        private FileService $fileService,
    )
    {
    }

    public function addFieldIDShortcode(string $type): TextField
    {
        $textField = TextField::new('id', new TranslatableMessage('Shortcode'));
        $textField->formatValue(fn ($identity): string => sprintf('[%s:%s]', $type . 'url', $identity));
        $textField->onlyOnDetail();

        return $textField;
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

    /**
     * Helper bundle returning the standard identity fields for most content entities.
     * Order is important for UI coherence.
     *
     * @return array<int, IdField|TextField|SlugField|BooleanField|ImageField|AssociationField>
     */
    public function baseIdentitySet(
        string $pageName,
        string $entityFqcn,
        bool $withSlug = true,
        bool $withImage = true,
        bool $withEnable = true,
    ): array
    {
        $fields   = [];
        $fields[] = $this->idField();
        if ($withSlug) {
            $fields[] = $this->slugField();
        }

        if ($withEnable) {
            $fields[] = $this->booleanField('enable', (string) new TranslatableMessage('Enable'));
        }

        $fields[] = $this->titleField();
        if ($withImage) {
            $fields[] = $this->imageField('img', $pageName, $entityFqcn);
        }

        return $fields;
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

    public function createdAtField(): DateTimeField
    {
        return DateTimeField::new('createdAt', new TranslatableMessage('Created At'))->hideWhenCreating();
    }

    /**
     * Date tab helper (tab + createdAt + updatedAt).
     *
     * @return array<int, mixed>
     */
    public function dateSet(string $pageName): array
    {
        if ('new' === $pageName) {
            return [];
        }

        return [
            FormField::addTab(new TranslatableMessage('Date')),
            $this->createdAtField(),
            $this->updatedAtField(),
        ];
    }

    /**
     * Full common content set (identity + taxonomy + optional paragraphs + meta + ref user).
     * Simplifies controllers migrating away from legacy wrappers.
     *
     * @return array<int, mixed>
     */
    public function fullContentSet(string $type, string $pageName, string $entityFqcn, bool $isSuperAdmin): array
    {
        return array_merge(
            $this->baseIdentitySet($pageName, $entityFqcn),
            $this->taxonomySet($type),
            $this->paragraphFields($pageName),
            $this->metaFields(),
            $this->refUserFields($isSuperAdmin)
        );
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
    ): ImageField|TextField
    {
        if ('edit' === $pageName || 'new' === $pageName) {
            $deleteLabel      = new TranslatableMessage('Delete image');
            $downloadLabel    = new TranslatableMessage('Download');
            $mimeTypesMessage = new TranslatableMessage('Please upload a valid image (JPEG, PNG, GIF, WebP).');
            $maxSizeMessage   = new TranslatableMessage(
                'The file is too large. Its size should not exceed {{ limit }}.'
            );

            $imageField = TextField::new($type . 'File', $label ?? new TranslatableMessage('Image'))->setFormType(
                VichImageType::class
            );
            $imageField->setFormTypeOptions(
                [
                    'required'       => false,
                    'allow_delete'   => true,
                    'delete_label'   => $deleteLabel->__toString(),
                    'download_label' => $downloadLabel->__toString(),
                    'download_uri'   => true,
                    'image_uri'      => true,
                    'asset_helper'   => true,
                    'constraints'    => [
                        new File(
                            [
                                'maxSize'          => ini_get('upload_max_filesize'),
                                'mimeTypes'        => [
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                    'image/webp',
                                ],
                                'mimeTypesMessage' => $mimeTypesMessage->__toString(),
                                'maxSizeMessage'   => $maxSizeMessage->__toString(),
                            ]
                        ),
                    ],
                ]
            );

            return $imageField;
        }

        $basePath = $this->fileService->getBasePath($entityFqcn, $type . 'File');

        return ImageField::new($type, $label ?? new TranslatableMessage('Image'))->setBasePath($basePath);
    }

    /**
     * @return array<int, FormField|TextField>
     */
    public function metaFields(): array
    {
        return [
            FormField::addTab(new TranslatableMessage('SEO')),
            TextField::new('meta.title', new TranslatableMessage('Title'))->hideOnIndex(),
            TextField::new('meta.keywords', new TranslatableMessage('Keywords'))->hideOnIndex(),
            TextField::new('meta.description', new TranslatableMessage('Description'))->hideOnIndex(),
        ];
    }

    /**
     * @return array<int, FormField|ParagraphsField>
     */
    public function paragraphFields(string $pageName): array
    {
        if ('new' === $pageName) {
            return [];
        }

        if ('edit' !== $pageName) {
            return [ParagraphsField::new('paragraphs', new TranslatableMessage('Paragraphs'))];
        }

        return [
            FormField::addTab(new TranslatableMessage('Paragraphs'))->hideWhenCreating(),
            ParagraphsField::new('paragraphs', new TranslatableMessage('Paragraphs'))->hideWhenCreating(),
        ];
    }

    /**
     * @return array<int, FormField|AssociationField>
     */
    public function refUserFields(bool $isSuperAdmin): array
    {
        if (!$isSuperAdmin) {
            return [];
        }

        $associationField = AssociationField::new('refuser', new TranslatableMessage('User'));
        $associationField->setSortProperty('username');

        return [
            FormField::addTab(new TranslatableMessage('User')),
            $associationField,
        ];
    }

    public function slugField(): SlugField
    {
        return SlugField::new('slug', new TranslatableMessage('Slug'))->hideOnIndex()->setFormTypeOptions(
            ['required' => false]
        )->setTargetFieldName('title')->setUnlockConfirmationMessage('Attention, si vous changez le titre, le slug sera modifié');
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

    public function updatedAtField(): DateTimeField
    {
        return DateTimeField::new('updatedAt', new TranslatableMessage('updated At'))->hideWhenCreating()->hideOnIndex();
    }

    public function workflowField(): TextField
    {
        return TextField::new('workflow', new TranslatableMessage('Workflow'))->setTemplatePath(
            'admin/field/workflow.html.twig'
        )->onlyOnIndex();
    }
}
