<?php

namespace Labstag\Field\Configurator\HttpLogs;

use Labstag\Field\HttpLogs\IsBotField;
use DeviceDetector\DeviceDetector;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;

final class IsBotConfigurator implements FieldConfiguratorInterface
{
    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        $instance = $entityDto->getInstance();
        if (is_null($instance)) {
            $fieldDto->setValue(false);

            return;
        }

        $deviceDetector = new DeviceDetector($instance->getAgent());
        $deviceDetector->parse();

        $fieldDto->setValue((bool) $deviceDetector->isBot());
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        return IsBotField::class === $fieldDto->getFieldFqcn();
    }
}
