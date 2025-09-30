<?php

namespace Labstag\FileStorage;

use Labstag\Entity\Page;
use Labstag\Interface\FileStorageInterface;
use Labstag\Lib\FileStorageLib;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

class PageFileStorage extends FileStorageLib
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.page.storage')]
        LocalFilesystemAdapter $adapter,
        KernelInterface $kernel
    )
    {
        parent::__construct($kernel);
        $this->setAdapter($adapter);
        $this->setType('page');
    }

    public function getEntity(): ?string
    {
        return Page::class;
    }
}