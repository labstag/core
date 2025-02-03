<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Chapter;
use Labstag\Entity\Configuration;
use Labstag\Entity\Edito;
use Labstag\Entity\Memo;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Paragraph;
use Labstag\Entity\Post;
use Labstag\Entity\Story;
use Labstag\Entity\User;
use Labstag\Lib\ServiceEntityRepositoryLib;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class FileService
{
    public function __construct(
        #[Autowire(service: 'flysystem.adapter.private.storage')]
        protected LocalFilesystemAdapter $privateAdapter,
        #[Autowire(service: 'flysystem.adapter.public.storage')]
        protected LocalFilesystemAdapter $publicAdapter,
        #[Autowire(service: 'flysystem.adapter.assets.storage')]
        protected LocalFilesystemAdapter $assetsAdapter,
        #[Autowire(service: 'flysystem.adapter.movie.storage')]
        protected LocalFilesystemAdapter $movieAdapter,
        #[Autowire(service: 'flysystem.adapter.configuration.storage')]
        protected LocalFilesystemAdapter $configurationAdapter,
        #[Autowire(service: 'flysystem.adapter.avatar.storage')]
        protected LocalFilesystemAdapter $avatarAdapter,
        #[Autowire(service: 'flysystem.adapter.chapter.storage')]
        protected LocalFilesystemAdapter $chapterAdapter,
        #[Autowire(service: 'flysystem.adapter.edito.storage')]
        protected LocalFilesystemAdapter $editoAdapter,
        #[Autowire(service: 'flysystem.adapter.story.storage')]
        protected LocalFilesystemAdapter $storyAdapter,
        #[Autowire(service: 'flysystem.adapter.memo.storage')]
        protected LocalFilesystemAdapter $memoAdapter,
        #[Autowire(service: 'flysystem.adapter.page.storage')]
        protected LocalFilesystemAdapter $pageAdapter,
        #[Autowire(service: 'flysystem.adapter.paragraph.storage')]
        protected LocalFilesystemAdapter $paragraphAdapter,
        #[Autowire(service: 'flysystem.adapter.post.storage')]
        protected LocalFilesystemAdapter $postAdapter,
        protected KernelInterface $kernel,
        protected EntityManagerInterface $entityManager,
        protected ParameterBagInterface $parameterBag,
        protected PropertyMappingFactory $propertyMappingFactory,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        $mappings         = $this->getMappingForEntity($entity);
        $file             = '';
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($mappings as $mapping) {
            if ($field != $mapping->getFileNamePropertyName()) {
                continue;
            }

            $basePath = $this->getBasePath($entity, $mapping->getFilePropertyName());
            $content  = $propertyAccessor->getValue($entity, $mapping->getFileNamePropertyName());
            if ('' != $content) {
                $file = $basePath.'/'.$content;
            }
        }

        return $file;
    }

    public function deleteAll(): void
    {
        $data = $this->getDataStorage();
        foreach (array_keys($data) as $type) {
            if (in_array($type, ['private', 'public', 'assets'])) {
                continue;
            }

            $adapter = $this->getAdapter($type);
            if (!$adapter instanceof LocalFilesystemAdapter) {
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

    public function deletedFileByEntities(): int
    {
        $total    = 0;
        $data     = $this->getFiles();
        $entities = $this->getEntity();
        foreach ($data as $type => $files) {
            $deletes    = [];
            $repository = $this->getRepository($entities[$type]);
            $mappings   = $this->propertyMappingFactory->fromObject(new $entities[$type]());
            foreach ($files as $row) {
                $file = $row['path'];
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

    public function getBasePath(mixed $entity, string $type): string
    {
        $object = $this->propertyMappingFactory->fromField(new $entity(), $type);

        return $object->getUriPrefix();
    }

    public function getFileInAdapter(string $type, string $fileName): ?string
    {
        $files = $this->getFilesByAdapter($type);
        $data  = null;
        foreach ($files as $file) {
            if ($file['content']->path() != $fileName) {
                continue;
            }

            $data = $file['folder'].'/'.$file['path'];

            break;
        }

        if (is_null($data)) {
            return $data;
        }

        return str_replace('%kernel.project_dir%', $this->parameterBag->get('kernel.project_dir'), $data);
    }

    /**
     * @return mixed[]
     */
    public function getFiles(): array
    {
        $files = [];
        $data  = $this->getDataStorage();
        foreach (array_keys($data) as $type) {
            if (in_array($type, ['private', 'public'])) {
                continue;
            }

            $files[$type] = $this->getFilesByAdapter($type);
        }

        return $files;
    }

    public function getFilesByAdapter(string $type): array
    {
        $adapter = $this->getAdapter($type);
        if (!$adapter instanceof LocalFilesystemAdapter) {
            throw new Exception('Adapter not found');
        }

        $filesystem = new Filesystem(
            $adapter,
            [
                'public_url' => $this->getFolder($type),
            ]
        );

        return $this->getFilesByDirectory($filesystem, '', $type);
    }

    public function getFullBasePath(mixed $entity, string $type): string
    {
        $basePath = $this->getBasePath($entity, $type);

        return $this->parameterBag->get('kernel.project_dir').'/public'.$basePath;
    }

    public function getInfoImage(string $file): array
    {
        $size = getimagesize($file);

        try {
            $mimetype = mime_content_type($file);
        } catch (Exception) {
            $mimetype = 'image/jpeg';
        }

        $public = str_replace(
            $this->parameterBag->get('kernel.project_dir').'/public',
            '',
            $file
        );
        $info = [
            'src'    => $file,
            'public' => $public,
            'data'   => [
                'width'  => $size[0],
                'height' => $size[1],
                'type'   => $mimetype,
            ],
        ];

        return $info;
    }

    /**
     * @param mixed[]|object $entity
     *
     * @return mixed[]
     */
    public function getMappingForEntity(object|array $entity): array
    {
        return $this->propertyMappingFactory->fromObject($entity);
    }

    protected function getRepository(string $entity): ServiceEntityRepositoryLib
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryLib) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }

    /**
     * @param mixed[] $files
     */
    private function deleteFilesByType(int|string $type, array $files): void
    {
        $adapter = $this->getAdapter($type);
        if (!$adapter instanceof LocalFilesystemAdapter) {
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

    private function getAdapter(string $type): ?LocalFilesystemAdapter
    {
        $data = $this->getDataStorage();

        return $data[$type] ?? null;
    }

    /**
     * @return mixed[]
     */
    private function getDataStorage(): array
    {
        return [
            'assets'        => $this->assetsAdapter,
            'private'       => $this->privateAdapter,
            'public'        => $this->publicAdapter,
            'movie'         => $this->movieAdapter,
            'configuration' => $this->configurationAdapter,
            'avatar'        => $this->avatarAdapter,
            'chapter'       => $this->chapterAdapter,
            'edito'         => $this->editoAdapter,
            'story'         => $this->storyAdapter,
            'memo'          => $this->memoAdapter,
            'page'          => $this->pageAdapter,
            'paragraph'     => $this->paragraphAdapter,
            'post'          => $this->postAdapter,
        ];
    }

    /**
     * @return mixed[]
     */
    private function getEntity(): array
    {
        return [
            'avatar'        => User::class,
            'chapter'       => Chapter::class,
            'movie'         => Movie::class,
            'configuration' => Configuration::class,
            'edito'         => Edito::class,
            'story'         => Story::class,
            'memo'          => Memo::class,
            'page'          => Page::class,
            'paragraph'     => Paragraph::class,
            'post'          => Post::class,
        ];
    }

    /**
     * @return mixed[]
     */
    private function getFilesByDirectory($filesystem, $directory, $type): array
    {
        $files            = [];
        $directoryListing = $filesystem->listContents($directory);
        foreach ($directoryListing as $content) {
            if ($content->isFile()) {
                $files[] = [
                    'filesystem' => $filesystem,
                    'content'    => $content,
                    'folder'     => $this->getFolder($type),
                    'path'       => $content->path(),
                ];

                continue;
            }

            $files = array_merge(
                $files,
                $this->getFilesByDirectory($filesystem, $content->path(), $type)
            );
        }

        return $files;
    }

    private function getFolder(string $type): mixed
    {
        $config = Yaml::parse(file_get_contents($this->kernel->getProjectDir().'/config/packages/flysystem.yaml'));

        $storages = $config['flysystem']['storages'];
        if (!array_key_exists($type.'.storage', $storages)) {
            throw new Exception('Type not found');
        }

        $storage = $storages[$type.'.storage'];

        return $storage['options']['directory'];
    }
}
