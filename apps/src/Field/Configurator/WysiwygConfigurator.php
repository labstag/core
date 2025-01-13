<?php

namespace Labstag\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Labstag\Field\WysiwygField;
use Override;

final class WysiwygConfigurator implements FieldConfiguratorInterface
{
    #[Override]
    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        unset($adminContext, $entityDto);
        $fieldDto->setFormTypeOptionIfNotSet('attr.rows', $fieldDto->getCustomOption(WysiwygField::OPTION_NUM_OF_ROWS));
        $fieldDto->setFormTypeOption('attr.class', $fieldDto->getFormTypeOption('attr.class') . ' wysiwyg');
    }

    #[Override]
    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return $fieldDto->getFieldFqcn() === WysiwygField::class;
    }
}
