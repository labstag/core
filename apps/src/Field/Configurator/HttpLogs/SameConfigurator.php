<?php

namespace Labstag\Field\Configurator\HttpLogs;

use Labstag\Field\HttpLogs\SameField;
use Labstag\Repository\HttpErrorLogsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;

final class SameConfigurator implements FieldConfiguratorInterface
{
    private HttpErrorLogsRepository $httpErrorLogsRepository;

    public function __construct(
        HttpErrorLogsRepository $httpErrorLogsRepository
    )
    {
        $this->httpErrorLogsRepository = $httpErrorLogsRepository;
    }

    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return SameField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $instance = $entityDto->getInstance();
        if (is_null($instance)) {
            $field->setValue(false);

            return;
        }

        $internetProtocol = $instance->getInternetProtocol();
        $logs = $this->httpErrorLogsRepository->findBy(['internetProtocol' => $internetProtocol]);

        $field->setValue(count($logs));
    }
}
