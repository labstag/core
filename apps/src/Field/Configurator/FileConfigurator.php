<?php

namespace Labstag\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Labstag\Field\FileField;

final class FileConfigurator implements FieldConfiguratorInterface
{
    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        unset($fieldDto, $entityDto, $adminContext);
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return FileField::class == $fieldDto->getFieldFqcn();
    }
}
