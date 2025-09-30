<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Edito;
use Labstag\Interface\FileStorageInterface;
use Labstag\Lib\FileStorageLib;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

class EditoFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.edito.storage')]
        LocalFilesystemAdapter $adapter,
        KernelInterface $kernel
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($adapter);
        $this->setType('edito');
    }

    public function getEntity(): ?string
    {
        return Edito::class;
    }
}