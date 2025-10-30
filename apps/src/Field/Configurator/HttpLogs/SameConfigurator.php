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

    /**
     * @var array<mixed>
     */
    private array $logs = [];

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
        if (isset($this->logs[$internetProtocol])) {
            $fieldDto->setValue($this->logs[$internetProtocol]);

            return;
        }

        $logs = $this->httpErrorLogsRepository->findBy(
            ['internetProtocol' => $internetProtocol]
        );

        $total = count($logs);

        $this->logs[$internetProtocol] = $total;

        $fieldDto->setValue($total);
    }

    public function supports(FieldDto $fieldDto, EntityDto $entityDto): bool
    {
        unset($entityDto);

        return SameField::class === $fieldDto->getFieldFqcn();
    }
}
