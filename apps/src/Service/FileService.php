<?php
namespace Labstag\Service;

use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class FileService
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.private.storage')]
        protected LocalFilesystemAdapter $privateAdapter,
        #[Autowire(service: 'flysystem.adapter.public.storage')]
        protected LocalFilesystemAdapter $publicAdapter,
        #[Autowire(service: 'flysystem.adapter.avatar.storage')]
        protected LocalFilesystemAdapter $avatarAdapter,
        #[Autowire(service: 'flysystem.adapter.page.storage')]
        protected LocalFilesystemAdapter $pageAdapter,
        #[Autowire(service: 'flysystem.adapter.public.storage')]
        protected LocalFilesystemAdapter $memoAdapter,
        #[Autowire(service: 'flysystem.adapter.history.storage')]
        protected LocalFilesystemAdapter $historyAdapter,
        #[Autowire(service: 'flysystem.adapter.post.storage')]
        protected LocalFilesystemAdapter $postAdapter,
        protected KernelInterface $kernel
    )
    {

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

    private function getAdapter($type)
    {
        return match ($type) {
            'private.storage' => $this->privateAdapter,
            'public.storage'  => $this->publicAdapter,
            'avatar.storage'  => $this->avatarAdapter,
            'page.storage'    => $this->pageAdapter,
            'memo.storage'    => $this->memoAdapter,
            'history.storage' => $this->historyAdapter,
            'post.storage'    => $this->postAdapter,
            default           => null,
        };
    }


    public function getFileSystem($type)
    {
        $adapter = $this->getAdapter($type);
        if (is_null($adapter)) {
            throw new Exception('Adapter not found');
        }

        $storage = new Filesystem(
            $adapter,
            [
                'public_url' => $this->getFolder($type)
            ]
        );
        $contents = $storage->listContents('');
        foreach ($contents as $content) {
            dump(
                [
                    $content,
                    $content->path(),
                    $content->fileSize(),
                    $storage->publicUrl($content->path()),
                ]
            );
        }
    }
}