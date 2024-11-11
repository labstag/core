<?php

namespace Labstag\Service;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Edito;
use Labstag\Entity\History;
use Labstag\Entity\Memo;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\User;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class FileService
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.private.storage')]
        protected LocalFilesystemAdapter $privateAdapter,
        #[Autowire(service: 'flysystem.adapter.public.storage')]
        protected LocalFilesystemAdapter $publicAdapter,
        #[Autowire(service: 'flysystem.adapter.avatar.storage')]
        protected LocalFilesystemAdapter $avatarAdapter,
        #[Autowire(service: 'flysystem.adapter.chapter.storage')]
        protected LocalFilesystemAdapter $chapterAdapter,
        #[Autowire(service: 'flysystem.adapter.edito.storage')]
        protected LocalFilesystemAdapter $editoAdapter,
        #[Autowire(service: 'flysystem.adapter.history.storage')]
        protected LocalFilesystemAdapter $historyAdapter,
        #[Autowire(service: 'flysystem.adapter.memo.storage')]
        protected LocalFilesystemAdapter $memoAdapter,
        #[Autowire(service: 'flysystem.adapter.page.storage')]
        protected LocalFilesystemAdapter $pageAdapter,
        #[Autowire(service: 'flysystem.adapter.paragraph.storage')]
        protected LocalFilesystemAdapter $paragraphAdapter,
        #[Autowire(service: 'flysystem.adapter.post.storage')]
        protected LocalFilesystemAdapter $postAdapter,
        protected KernelInterface $kernel,
        protected ManagerRegistry $managerRegistry,
        protected ParameterBagInterface $parameterBag,
        protected PropertyMappingFactory $propertyMappingFactory
    )
    {
    }

    public function all()
    {
        $files = [];
        $data  = $this->getDataStorage();
        foreach (array_keys($data) as $key) {
            if (in_array($key, ['private', 'public'])) {
                continue;
            }

            $files = array_merge($files, $this->getFileSystem($key));
        }

        return $files;
    }

    public function deleteAll()
    {
        $data = $this->getDataStorage();
        foreach (array_keys($data) as $type) {
            if (in_array($type, ['private', 'public'])) {
                continue;
            }

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
                $filesystem->delete($content->path());
            }
        }
    }

    public function deletedFileByEntities()
    {
        $total    = 0;
        $data     = $this->getFiles();
        $entities = $this->getEntity();
        foreach ($data as $type => $files) {
            $deletes    = [];
            $repository = $this->getRepository($entities[$type]);
            $mappings   = $this->propertyMappingFactory->fromObject(new $entities[$type]());
            foreach ($files as $file) {
                $find = 0;
                foreach ($mappings as $mapping) {
                    $field  = $mapping->getFileNamePropertyName();
                    $entity = $repository->findOneBy(
                        [$field => $file]
                    );
                    if (!$entity instanceof $entities[$type]) {
                        continue;
                    }

                    $find = 1;

                    break;
                }

                if (0 == $find) {
                    $deletes[] = $file;
                }
            }

            $total += count($deletes);
            $this->deleteFilesByType($type, $deletes);
        }

        return $total;
    }

    public function getBasePath($entity, $type)
    {
        $object = $this->propertyMappingFactory->fromField(new $entity(), $type);

        return $object->getUriPrefix();
    }

    public function getFiles()
    {
        $files = [];
        $data  = $this->getDataStorage();
        foreach (array_keys($data) as $type) {
            if (in_array($type, ['private', 'public'])) {
                continue;
            }

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
            $files[$type]     = [];
            $directoryListing = $filesystem->listContents('');
            foreach ($directoryListing as $content) {
                $files[$type][] = $content->path();
            }
        }

        return $files;
    }

    public function getFileSystem($type)
    {
        $files   = [];
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
            $files[] = $filesystem->publicUrl($content->path());
        }

        return $files;
    }

    public function getFullBasePath($entity, $type)
    {
        $basePath = $this->getBasePath($entity, $type);

        return $this->parameterBag->get('kernel.project_dir').'/public'.$basePath;
    }

    public function getMappingForEntity($entity)
    {
        return $this->propertyMappingFactory->fromObject($entity);
    }

    protected function getRepository(string $entity)
    {
        return $this->managerRegistry->getRepository($entity);
    }

    private function deleteFilesByType($type, $files)
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
            if (in_array($content->path(), $files)) {
                $filesystem->delete($content->path());
            }
        }
    }

    private function getAdapter($type)
    {
        $data = $this->getDataStorage();

        return $data[$type] ?? null;
    }

    private function getDataStorage(): array
    {
        return [
            'private'   => $this->privateAdapter,
            'public'    => $this->publicAdapter,
            'avatar'    => $this->avatarAdapter,
            'chapter'   => $this->chapterAdapter,
            'edito'     => $this->editoAdapter,
            'history'   => $this->historyAdapter,
            'memo'      => $this->memoAdapter,
            'page'      => $this->pageAdapter,
            'paragraph' => $this->paragraphAdapter,
            'post'      => $this->postAdapter,
        ];
    }

    private function getEntity(): array
    {
        return [
            'avatar'    => User::class,
            'chapter'   => Chapter::class,
            'edito'     => Edito::class,
            'history'   => History::class,
            'memo'      => Memo::class,
            'page'      => Page::class,
            'paragraph' => Paragraph::class,
            'post'      => Post::class,
        ];
    }

    private function getFolder($type)
    {
        $config = Yaml::parse(
            file_get_contents($this->kernel->getProjectDir().'/config/packages/flysystem.yaml')
        );

        $storages = $config['flysystem']['storages'];
        if (!array_key_exists($type.'.storage', $storages)) {
            throw new Exception('Type not found');
        }

        $storage = $storages[$type.'.storage'];

        return str_replace(
            '%kernel.project_dir%/public',
            '',
            $storage['options']['directory']
        );
    }
}
