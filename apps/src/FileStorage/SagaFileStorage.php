<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Saga;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class SagaFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.saga.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('saga');
    }

    #[Override]
    public function getEntity(): array
    {
        return [Saga::class];
    }
}
