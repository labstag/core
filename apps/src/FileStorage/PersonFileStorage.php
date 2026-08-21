<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Person;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class PersonFileStorage extends FileStorageAbstract implements FileStorageInterface
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.person.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('person');
    }

    #[Override]
    public function getEntity(): array
    {
        return [Person::class];
    }
}
