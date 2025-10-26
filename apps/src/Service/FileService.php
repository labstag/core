<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Repository\ServiceEntityRepositoryAbstract;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

final class FileService
{
    public function __construct(
        /**
         * @var iterable<\Labstag\FileStorage\Abstract\FileStorageLib>
         */
        #[AutowireIterator('labstag.filestorage')]
        private readonly iterable $fileStorages,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private PropertyMappingFactory $propertyMappingFactory,
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
                $file = $basePath . '/' . $content;
            }
        }

        return $file;
    }

    public function deleteAll(): void
    {
        foreach ($this->fileStorages as $fileStorage) {
            $type = $fileStorage->getType();
            if (in_array($type, ['private', 'public', 'assets'])) {
                continue;
            }

            $filesystem       = $fileStorage->getFilesystem();
            $directoryListing = $filesystem->listContents('');
            foreach ($directoryListing as $content) {
                $filesystem->delete($content->path());
            }
        }
    }

    public function deletedFileByEntities(): int
    {
        $total = 0;
        foreach ($this->fileStorages as $fileStorage) {
            $deletes     = [];
            $entityClass = $fileStorage->getEntity();
            if (is_null($entityClass)) {
                continue;
            }

            $repository = $this->getRepository($entityClass);
            $mappings   = $this->propertyMappingFactory->fromObject(new $entityClass());
            $files      = $fileStorage->getFilesByDirectory($fileStorage->getFilesystem(), '');
            foreach ($files as $row) {
                $file = $row['path'];
                $find = 0;
                foreach ($mappings as $mapping) {
                    $field  = $mapping->getFileNamePropertyName();
                    $entity = $repository->findOneBy(
                        [$field => $file]
                    );
                    if (!$entity instanceof $entityClass) {
                        continue;
                    }

                    $find = 1;

                    break;
                }

                if (0 === $find) {
                    $deletes[] = $file;
                }
            }

            $total += count($deletes);
            $fileStorage->deleteFilesByType($deletes);
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
        $fileSystem = null;
        foreach ($this->fileStorages as $fileStorage) {
            if ($fileStorage->getType() == $type) {
                $fileSystem = $fileStorage->getFilesystem();
            }
        }

        if (is_null($fileSystem) || !$fileSystem->has($fileName)) {
            return null;
        }

        return str_replace(
            '%kernel.project_dir%',
            $this->parameterBag->get('kernel.project_dir'),
            $fileSystem->publicUrl($fileName)
        );
    }

    /**
     * @return mixed[]
     */
    public function getFiles(): array
    {
        $files = [];
        foreach ($this->fileStorages as $fileStorage) {
            $type = $fileStorage->getType();
            if (in_array($type, ['private', 'public'])) {
                continue;
            }

            $files[$type] = $fileStorage->getFilesByDirectory($fileStorage->getFilesystem(), '');
        }

        return $files;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilesByAdapter(string $type, string $file = ''): array
    {
        foreach ($this->fileStorages as $fileStorage) {
            if ($fileStorage->getType() == $type) {
                return $fileStorage->getFilesByDirectory($fileStorage->getFilesystem(), $file);
            }
        }

        return [];
    }

    public function getFullBasePath(mixed $entity, string $type): string
    {
        $basePath = $this->getBasePath($entity, $type);

        return $this->parameterBag->get('kernel.project_dir') . '/public' . $basePath;
    }

    /**
     * @return array<string, mixed>
     */
    public function getInfoImage(string $file): array
    {
        $size = getimagesize($file);

        try {
            $mimetype = mime_content_type($file);
        } catch (Exception) {
            $mimetype = 'image/jpeg';
        }

        $public = str_replace($this->parameterBag->get('kernel.project_dir') . '/public', '', $file);

        return [
            'src'    => $file,
            'public' => $public,
            'data'   => [
                'width'  => $size[0],
                'height' => $size[1],
                'type'   => $mimetype,
            ],
        ];
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

    /**
     * @return ServiceEntityRepositoryAbstract<object>
     */
    private function getRepository(string $entity): ServiceEntityRepositoryAbstract
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (!$entityRepository instanceof ServiceEntityRepositoryAbstract) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }
}
