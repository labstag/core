<?php

namespace Labstag\Field\Configurator\HttpLogs;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Labstag\Field\HttpLogs\SameField;
use Labstag\Repository\HttpErrorLogsRepository;

final class SameConfigurator implements FieldConfiguratorInterface
{
    public function __construct(
        private HttpErrorLogsRepository $httpErrorLogsRepository,
    )
    {
    }

    public function configure(FieldDto $fieldDto, EntityDto $entityDto, AdminContext $adminContext): void
    {
        unset($adminContext);
        $instance = $entityDto->getInstance();
        if (is_null($instance)) {
            $fieldDto->setValue(false);

            return;
        }

        $internetProtocol = $instance->getInternetProtocol();
        $logs             = $this->httpErrorLogsRepository->findBy(
            ['internetProtocol' => $internetProtocol]
        );

        $fieldDto->setValue(count($logs));
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return SameField::class === $fieldDto->getFieldFqcn();
    }
}
