<?php

// phpcs:ignoreFile

namespace Labstag\Field\Configurator;

use Doctrine\Common\Util\ClassUtils;
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
use Labstag\Field\MetaParentField;
use Labstag\Service\MetaService;
use Override;
use RuntimeException;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Translation\TranslatorInterface;
use Traversable;

final readonly class MetaParentConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private EntityFactory $entityFactory,
        private AdminUrlGenerator $adminUrlGenerator,
        private TranslatorInterface $translator,
        private MetaService $metaService
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    #[Override]
    public function configure(
        FieldDto $fieldDto,
        EntityDto $entityDto,
        AdminContext $adminContext
    ): void {
        $instance = $entityDto->getInstance();
        $object   = $this->metaService->getEntityParent($instance);
        if (is_null($object->value) || is_null($object->name) || is_null($object)) {
            return;
        }

        $fieldDto->setValue($object->value);
        $fieldDto->setProperty($object->name);
        $fieldDto->getDoctrineMetadata()->set('targetEntity', ClassUtils::getClass($object->value));
        if (!$entityDto->isAssociation($object->name)) {
            throw new RuntimeException(sprintf('The "%s" field is not a Doctrine association, so it cannot be used as an association field.', $object->name));
        }

        $targetEntityFqcn = $fieldDto->getDoctrineMetadata()->get('targetEntity');
        // the target CRUD controller can be NULL; in that case, field value doesn't link to the related entity
        $targetCrudControllerFqcn = $fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER) ?? $adminContext->getCrudControllers()->findCrudFqcnByEntityFqcn($targetEntityFqcn);
        $fieldDto->setCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER, $targetCrudControllerFqcn);

        if (MetaParentField::WIDGET_AUTOCOMPLETE === $fieldDto->getCustomOption(MetaParentField::OPTION_WIDGET)) {
            $fieldDto->setFormTypeOption('attr.data-ea-widget', 'ea-autocomplete');
        }

        // check for embedded associations
        $propertyNameParts = explode('.', (string) $object->name);
        $this->configureFirst($entityDto, $propertyNameParts, $fieldDto, $object->name);
        if (true === $fieldDto->getCustomOption(MetaParentField::OPTION_AUTOCOMPLETE)) {
            $targetCrudControllerFqcn = $fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER);
            if (null === $targetCrudControllerFqcn) {
                $message = sprintf(
                    'The "%s" field cannot be autocompleted because it doesn\'t define the related CRUD controller FQCN with the "setCrudController()" method.',
                    $fieldDto->getProperty()
                );

                throw new RuntimeException($message);
            }

            $fieldDto->setFormType(CrudAutocompleteType::class);
            $autocompleteEndpointUrl = $this->adminUrlGenerator->unsetAll()->set('page', 1);
            // The autocomplete should always start on the first page
            $autocompleteEndpointUrl->setController(
                $fieldDto->getCustomOption(MetaParentField::OPTION_CRUD_CONTROLLER)
            );
            $autocompleteEndpointUrl->setAction('autocomplete')->set(
                MetaParentField::PARAM_AUTOCOMPLETE_CONTEXT,
                [
                    EA::CRUD_CONTROLLER_FQCN => $adminContext->getRequest()->query->get(EA::CRUD_CONTROLLER_FQCN),
                    'propertyName'           => $object->name,
                    'originatingPage'        => $adminContext->getCrud()->getCurrentPage(),
                ]
            );
            $autocompleteEndpointUrl->generateUrl();

            $fieldDto->setFormTypeOption('attr.data-ea-autocomplete-endpoint-url', $autocompleteEndpointUrl);

            return;
        }

        $fieldDto->setFormTypeOptionIfNotSet(
            'query_builder',
            static function (EntityRepository $entityRepository) use ($fieldDto): QueryBuilder
            {
                $queryBuilder = $entityRepository->createQueryBuilder('entity');
                if ($queryBuilderCallable = $fieldDto->getCustomOption(MetaParentField::OPTION_QUERY_BUILDER_CALLABLE)) {
                    $queryBuilderCallable($queryBuilder);
                }

                return $queryBuilder;
            }
        );
    }

    #[Override]
    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return MetaParentField::class === $fieldDto->getFieldFqcn();
    }

    private function configureFirst(\EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto &$entityDto, &$propertyNameParts, \EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto &$fieldDto, string &$propertyName): void
    {
        if (count($propertyNameParts) <= 1) {
            if ($entityDto->isToOneAssociation($propertyName)) {
                $this->configureToOneAssociation($fieldDto);
            }

            if ($entityDto->isToManyAssociation($propertyName)) {
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
                throw new RuntimeException(sprintf('There is no association for the class "%s" with name "%s"', $targetEntityFqcn, $propertyNamePart));
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
                $propertyName.'.'.$metadata->getIdentifierFieldNames()[0]
            );
            $relatedEntityDto = $this->entityFactory->create(
                $targetEntityFqcn,
                $relatedEntityId
            );

            $fieldDto->setCustomOption(
                MetaParentField::OPTION_RELATED_URL,
                $this->generateLinkToAssociatedEntity(
                    $targetCrudControllerFqcn,
                    $relatedEntityDto
                )
            );
            $fieldDto->setFormattedValue(
                $this->formatAsString(
                    $relatedEntityDto->getInstance(),
                    $relatedEntityDto
                )
            );
        } catch (UnexpectedTypeException) {
            throw new RuntimeException(sprintf('The property "%s" is not accessible in the entity "%s"', $propertyName, $entityDto->getFqcn()));
        }
    }

    private function configureToManyAssociation(FieldDto $fieldDto): void
    {
        $fieldDto->setCustomOption(MetaParentField::OPTION_DOCTRINE_ASSOCIATION_TYPE, 'toMany');

        // associations different from *-to-one cannot be sorted
        $fieldDto->setSortable(false);

        $fieldDto->setFormTypeOptionIfNotSet('multiple', true);

        // @var PersistentCollection $collection
        $fieldDto->setFormTypeOptionIfNotSet(
            'class',
            $fieldDto->getDoctrineMetadata()->get('targetEntity')
        );

        if (null === $fieldDto->getTextAlign()) {
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

        $targetEntityFqcn         = $fieldDto->getDoctrineMetadata()->get('targetEntity');
        $targetCrudControllerFqcn = $fieldDto->getCustomOption(
            MetaParentField::OPTION_CRUD_CONTROLLER
        );

        $fieldToValue  = $fieldDto->getValue();
        $entityFactory = $this->entityFactory;

        $targetEntityDto = null === $fieldToValue ? $entityFactory->create($targetEntityFqcn) : $entityFactory->createForEntityInstance($fieldToValue);
        $fieldDto->setFormTypeOptionIfNotSet('class', $targetEntityDto->getFqcn());

        $fieldDto->setCustomOption(
            MetaParentField::OPTION_RELATED_URL,
            $this->generateLinkToAssociatedEntity($targetCrudControllerFqcn, $targetEntityDto)
        );

        $fieldDto->setFormattedValue($this->formatAsString($fieldToValue, $targetEntityDto));
    }

    private function countNumElements($collection): int
    {
        if (null === $collection) {
            return 0;
        }

        if (is_countable($collection)) {
            return \count($collection);
        }

        if ($collection instanceof Traversable) {
            return iterator_count($collection);
        }

        return 0;
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    private function formatAsString($entityInstance, EntityDto $entityDto): ?string
    {
        if (null === $entityInstance) {
            return null;
        }

        if (method_exists($entityInstance, '__toString')) {
            return (string) $entityInstance;
        }

        if (null !== $primaryKeyValue = $entityDto->getPrimaryKeyValue()) {
            return sprintf('%s #%s', $entityDto->getName(), $primaryKeyValue);
        }

        return $entityDto->getName();
    }

    private function generateLinkToAssociatedEntity(?string $crudController, EntityDto $entityDto): ?string
    {
        if (null === $crudController) {
            return null;
        }

        $url = $this->adminUrlGenerator;
        $url->setController($crudController);
        $url->setAction(Action::DETAIL);
        $url->setEntityId($entityDto->getPrimaryKeyValue());
        $url->includeReferrer();

        return $url->generateUrl();
    }
}
