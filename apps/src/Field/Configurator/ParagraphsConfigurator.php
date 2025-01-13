<?php

// phpcs:ignoreFile

namespace Labstag\Field\Configurator;

use Doctrine\Common\Util\ClassUtils;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Labstag\Field\ParagraphsField;
use Labstag\Service\ParagraphService;
use Override;

final class ParagraphsConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private ParagraphService $paragraphService,
    ) {
    }

    #[Override]
    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        // if label is NULL, its value is autogenerated from the property name.
        // But property names don't make sense for this kind of special field, so
        // make the label FALSE to not display it
        $fieldDto->setLabel('Paragraphes');
        if (is_null($fieldDto->getLabel())) {
            $fieldDto->setLabel(false);
        }

        $crudControllerRegistry = $adminContext->getCrudControllers();

        $instance = $entityDto->getInstance();
        if (is_null($instance)) {
            return;
        }

        $classInstance = ClassUtils::getClass($instance);
        $controller = $crudControllerRegistry->findCrudFqcnByEntityFqcn($classInstance);
        $fieldDto->setCustomOption('controller', $controller);
        $paragraphs = $this->paragraphService->getAll($classInstance);
        $fieldDto->setCustomOption('paragraphs', $paragraphs);

        $breakpointName = $fieldDto->getCustomOption(ParagraphsField::OPTION_ROW_BREAKPOINT);

        $cssClasses = ($breakpointName === '') ? 'flex-fill' : sprintf(
            'd-none d-%s-flex flex-%s-fill',
            $breakpointName,
            $breakpointName
        );

        $fieldDto->setFormTypeOption(
            'row_attr.class',
            $fieldDto->getFormTypeOption('row_attr.class') . ' ' . $cssClasses
        );
    }

    #[Override]
    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return $fieldDto->getFieldFqcn() === ParagraphsField::class;
    }
}
