<?php

namespace Labstag\Field\Configurator;

use Labstag\Field\WysiwygField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;

final class WysiwygConfigurator implements FieldConfiguratorInterface
{
    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        $fieldDto->setFormTypeOptionIfNotSet('attr.rows', $fieldDto->getCustomOption(WysiwygField::OPTION_NUM_OF_ROWS));
        $fieldDto->setFormTypeOption('attr.class', $fieldDto->getFormTypeOption('attr.class').' wysiwyg');
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        return WysiwygField::class === $fieldDto->getFieldFqcn();
    }
}
