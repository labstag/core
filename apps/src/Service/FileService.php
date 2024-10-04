<?php

namespace Labstag\Service;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class FileService
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.private.storage')]
        protected LocalFilesystemAdapter $privateAdapter,
        #[Autowire(service: 'flysystem.adapter.edito.storage')]
        protected LocalFilesystemAdapter $editoAdapter,
        #[Autowire(service: 'flysystem.adapter.public.storage')]
        protected LocalFilesystemAdapter $publicAdapter,
        #[Autowire(service: 'flysystem.adapter.avatar.storage')]
        protected LocalFilesystemAdapter $avatarAdapter,
        #[Autowire(service: 'flysystem.adapter.chapter.storage')]
        protected LocalFilesystemAdapter $chapterAdapter,
        #[Autowire(service: 'flysystem.adapter.page.storage')]
        protected LocalFilesystemAdapter $pageAdapter,
        #[Autowire(service: 'flysystem.adapter.memo.storage')]
        protected LocalFilesystemAdapter $memoAdapter,
        #[Autowire(service: 'flysystem.adapter.history.storage')]
        protected LocalFilesystemAdapter $historyAdapter,
        #[Autowire(service: 'flysystem.adapter.post.storage')]
        protected LocalFilesystemAdapter $postAdapter,
        protected KernelInterface $kernel
    )
    {
    }

    public function all()
    {
        $data = $this->getData();
        foreach (array_keys($data) as $key) {
            dump($key);
            $this->getFileSystem($key);
        }
    }

    public function getFileSystem($type)
    {
        $adapter = $this->getAdapter($type);
        if (is_null($adapter)) {
            throw new Exception('Adapter not found');
        }

        $filesystem = new Filesystem(
            $adapter,
            [
                'public_url' => $this->getFolder($type),
            ]
        );
        $directoryListing = $filesystem->listContents('');
        foreach ($directoryListing as $content) {
            dump(
                [
                    $content,
                    $content->path(),
                    $filesystem->publicUrl($content->path()),
                ]
            );
        }
    }

    private function getAdapter($type)
    {
        $data = $this->getData();

        return $data[$type] ?? null;
    }

    private function getData(): array
    {
        return [
            'avatar.storage'  => $this->avatarAdapter,
            'chapter.storage' => $this->chapterAdapter,
            'edito.storage'   => $this->editoAdapter,
            'history.storage' => $this->historyAdapter,
            'memo.storage'    => $this->memoAdapter,
            'page.storage'    => $this->pageAdapter,
            'post.storage'    => $this->postAdapter,
            'private.storage' => $this->privateAdapter,
            'public.storage'  => $this->publicAdapter,
        ];
    }

    private function getFolder($type)
    {
        $config = Yaml::parse(
            file_get_contents($this->kernel->getProjectDir().'/config/packages/flysystem.yaml')
        );

        $storages = $config['flysystem']['storages'];
        if (!array_key_exists($type, $storages)) {
            throw new Exception('Type not found');
        }

        $storage = $storages[$type];

        return str_replace(
            '%kernel.project_dir%/public',
            '',
            $storage['options']['directory']
        );
    }
}
