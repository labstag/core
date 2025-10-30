<?php

// phpcs:ignoreFile

namespace Labstag\Field\Configurator;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\TextAlign;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CrudAutocompleteType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Gedmo\Tool\ClassUtils;
use Labstag\Field\MetaParentField;
use Labstag\Service\MetaService;
use Override;
use RuntimeException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Translation\TranslatorInterface;
use Traversable;

final class MetaParentConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private EntityFactory $entityFactory,
        private AdminUrlGenerator $adminUrlGenerator,
        private TranslatorInterface $translator,
        private MetaService $metaService,
    ) {
    }

    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        $instance = $entityDto->getInstance();
        $object   = $this->metaService->getEntityParent($instance);
        if (is_null($object->value) || is_null($object->name) || is_null($object)) {
            return;
        }

        $fieldDto->setValue($object->value);
        $fieldDto->setProperty($object->name);
        $fieldDto->getDoctrineMetadata()
            ->set('targetEntity', ClassUtils::getClass($object->value));
        if (!$entityDto->isAssociation($object->name)) {
            throw new RuntimeException(sprintf(
                'The "%s" field is not a Doctrine association, so it cannot be used as an association field.',
                $object->name
            ));
        }

        $targetEntityFqcn = $fieldDto->getDoctrineMetadata()
            ->get('targetEntity');
        // the target CRUD controller can be NULL; in that case, field value doesn't link to the related entity
        $targetCrudControllerFqcn = $fieldDto->getCustomOption(
            MetaParentField::OPTION_CRUD_CONTROLLER
        ) ?? $adminContext->getCrudControllers()
            ->findCrudFqcnByEntityFqcn($targetEntityFqcn);
        $fieldDto->setCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER, $targetCrudControllerFqcn);

        if (MetaParentField::WIDGET_AUTOCOMPLETE === $fieldDto->getCustomOption(MetaParentField::OPTION_WIDGET)) {
            $fieldDto->setFormTypeOption('attr.data-ea-widget', 'ea-autocomplete');
        }

        // check for embedded associations
        $propertyNameParts = explode('.', (string) $object->name);
        $this->configureFirst($entityDto, $propertyNameParts, $fieldDto, $object->name);
        if (true === $fieldDto->getCustomOption(MetaParentField::OPTION_AUTOCOMPLETE)) {
            $this->configureAutocomplete($fieldDto, $adminContext, $object);

            return;
        }

        $this->configureLast($fieldDto);
    }

    public function generateLinkToAssociatedEntity(?string $crudController, EntityDto $entityDto): ?string
    {
        if (is_null($crudController)) {
            return null;
        }

        $url = $this->adminUrlGenerator;
        $url->setController($crudController);
        $url->setAction(Action::DETAIL);
        $url->setEntityId($entityDto->getPrimaryKeyValue());

        return $url->generateUrl();
    }

    #[Override]
    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return MetaParentField::class === $fieldDto->getFieldFqcn();
    }

    private function configureAutocomplete(FieldDto $fieldDto, AdminContext $adminContext, object $object): void
    {
        $targetCrudControllerFqcn = $fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER);
        if (is_null($targetCrudControllerFqcn)) {
            $message = sprintf(
                'The "%s" field cannot be autocompleted because it doesn\'t define the related CRUD controller FQCN with the "setCrudController()" method.',
                $fieldDto->getProperty()
            );

            throw new RuntimeException($message);
        }

        $fieldDto->setFormType(CrudAutocompleteType::class);
        $adminUrlGenerator = $this->adminUrlGenerator->unsetAll()
            ->set('page', 1);
        // The autocomplete should always start on the first page
        $adminUrlGenerator->setController($fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER));
        $adminUrlGenerator->setAction('autocomplete')
            ->set(
                MetaParentField::PARAM_AUTOCOMPLETE_CONTEXT,
                [
                    EA::CRUD_CONTROLLER_FQCN => $adminContext->getRequest()->query->get(EA::CRUD_CONTROLLER_FQCN),
                    'propertyName'           => $object->name,
                    'originatingPage'        => $adminContext->getCrud()
                        ->getCurrentPage(),
                ]
            );
        $adminUrlGenerator->generateUrl();

        $fieldDto->setFormTypeOption('attr.data-ea-autocomplete-endpoint-url', $adminUrlGenerator);
    }

    /**
     * @param array<string> $propertyNameParts
     */
    private function configureFirst(
        EntityDto &$entityDto,
        array &$propertyNameParts,
        FieldDto &$fieldDto,
        string &$propertyName,
    ): void {
        if (1 >= count($propertyNameParts)) {
            if ($entityDto->getClassMetadata()->isSingleValuedAssociation($propertyName)) {
                $this->configureToOneAssociation($fieldDto);
            }

            if ($entityDto->getClassMetadata()->isSingleValuedAssociation($propertyName)) {
                $this->configureToManyAssociation($fieldDto);
            }

            return;
        }

        // prepare starting class for association
        $targetEntityFqcn = $entityDto->getPropertyMetadata($propertyNameParts[0]);
        $targetEntityFqcn = $targetEntityFqcn->get('targetEntity');
        array_shift($propertyNameParts);
        $metadata = $this->entityFactory->getEntityMetadata($targetEntityFqcn);

        foreach ($propertyNameParts as $propertyNamePart) {
            if (!$metadata->hasAssociation($propertyNamePart)) {
                throw new RuntimeException(sprintf(
                    'There is no association for the class "%s" with name "%s"',
                    $targetEntityFqcn,
                    $propertyNamePart
                ));
            }

            // overwrite next class from association
            $targetEntityFqcn = $metadata->getAssociationTargetClass($propertyNamePart);

            // read next association metadata
            $metadata = $this->entityFactory->getEntityMetadata($targetEntityFqcn);
        }

        $propertyAccessor         = new PropertyAccessor();
        $targetCrudControllerFqcn = $fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER);

        $fieldDto->setFormTypeOptionIfNotSet('class', $targetEntityFqcn);

        try {
            $relatedEntityId = $propertyAccessor->getValue(
                $entityDto->getInstance(),
                $propertyName . '.' . $metadata->getIdentifierFieldNames()[0]
            );
            $relatedEntityDto = $this->entityFactory->create($targetEntityFqcn, $relatedEntityId);

            $fieldDto->setCustomOption(
                MetaParentField::OPTION_RELATED_URL,
                $this->generateLinkToAssociatedEntity($targetCrudControllerFqcn, $relatedEntityDto)
            );
            $fieldDto->setFormattedValue($this->formatAsString($relatedEntityDto->getInstance()));
        } catch (UnexpectedTypeException) {
            throw new RuntimeException(sprintf(
                'The property "%s" is not accessible in the entity "%s"',
                $propertyName,
                $entityDto->getFqcn()
            ));
        }
    }

    private function configureLast(FieldDto $fieldDto): void
    {
        $fieldDto->setFormTypeOptionIfNotSet(
            'query_builder',
            static function (EntityRepository $entityRepository) use ($fieldDto): QueryBuilder
            {
                $queryBuilder         = $entityRepository->createQueryBuilder('entity');
                $queryBuilderCallable = $fieldDto->getCustomOption(MetaParentField::OPTION_QUERY_BUILDER_CALLABLE);
                if (is_object($queryBuilderCallable)) {
                    $queryBuilderCallable($queryBuilder);
                }

                return $queryBuilder;
            }
        );
    }

    private function configureToManyAssociation(FieldDto $fieldDto): void
    {
        $fieldDto->setCustomOption(MetaParentField::OPTION_DOCTRINE_ASSOCIATION_TYPE, 'toMany');

        // associations different from *-to-one cannot be sorted
        $fieldDto->setSortable(false);

        $fieldDto->setFormTypeOptionIfNotSet('multiple', true);

        // @var PersistentCollection $collection
        $fieldDto->setFormTypeOptionIfNotSet('class', $fieldDto->getDoctrineMetadata()->get('targetEntity'));

        if (is_null($fieldDto->getTextAlign())) {
            $fieldDto->setTextAlign(TextAlign::RIGHT);
        }

        $fieldDto->setFormattedValue($this->countNumElements($fieldDto->getValue()));
    }

    private function configureToOneAssociation(FieldDto $fieldDto): void
    {
        $fieldDto->setCustomOption(MetaParentField::OPTION_DOCTRINE_ASSOCIATION_TYPE, 'toOne');

        if (false === $fieldDto->getFormTypeOption('required')) {
            $fieldDto->setFormTypeOptionIfNotSet(
                'attr.placeholder',
                $this->translator->trans('label.form.empty_value', [], 'EasyAdminBundle')
            );
        }

        $targetEntityFqcn = $fieldDto->getDoctrineMetadata()
            ->get('targetEntity');
        $targetCrudControllerFqcn = $fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER);

        $fieldToValue  = $fieldDto->getValue();
        $entityFactory = $this->entityFactory;

        $targetEntityDto = is_null($fieldToValue) ? $entityFactory->create(
            $targetEntityFqcn
        ) : $entityFactory->createForEntityInstance($fieldToValue);
        $fieldDto->setFormTypeOptionIfNotSet('class', $targetEntityDto->getFqcn());

        $fieldDto->setCustomOption(
            MetaParentField::OPTION_RELATED_URL,
            $this->generateLinkToAssociatedEntity($targetCrudControllerFqcn, $targetEntityDto)
        );

        $fieldDto->setFormattedValue($this->formatAsString($fieldToValue));
    }

    private function countNumElements(mixed $collection): int
    {
        if (is_null($collection)) {
            return 0;
        }

        if (is_countable($collection)) {
            return count($collection);
        }

        if ($collection instanceof Traversable) {
            return iterator_count($collection);
        }

        return 0;
    }

    private function formatAsString(mixed $entityInstance): string
    {
        if (is_null($entityInstance)) {
            return '';
        }

        return (string) $entityInstance;
    }
}
