<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Company;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class CompanyFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.company.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('company');
    }

    #[Override]
    public function getEntity(): array
    {
        return [Company::class];
    }
}
