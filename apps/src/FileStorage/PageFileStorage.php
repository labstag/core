<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Page;
use Labstag\FileStorage\Abstract\FileStorageLib;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;

class PageFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.page.storage')]
        LocalFilesystemAdapter $localFilesystemAdapter,
        KernelInterface $kernel,
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($localFilesystemAdapter);
        $this->setType('page');
    }

    public function getEntity(): ?string
    {
        return Page::class;
    }
}
