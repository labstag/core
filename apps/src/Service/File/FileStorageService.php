<?php

declare(strict_types=1);

namespace Labstag\Service\File;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

final class FileStorageService
{
    public function __construct(
        #[AutowireIterator('labstag.filestorage')]
        private readonly iterable $fileStorages,
        private ParameterBagInterface $parameterBag,
        private PropertyMappingFactory $propertyMappingFactory,
    ) {
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

    public function getFilesByAdapter(string $type, string $file = ''): array
    {
        foreach ($this->fileStorages as $fileStorage) {
            if ($fileStorage->getType() == $type) {
                return $fileStorage->getFilesByDirectory($fileStorage->getFilesystem(), $file);
            }
        }

        return [];
    }

    public function saveFileInAdapter(string $type, string $fileName, $content): void
    {
        foreach ($this->fileStorages as $fileStorage) {
            if ($fileStorage->getType() === $type) {
                $fileStorage->saveFileInAdapter($fileName, $content);

                return;
            }
        }
    }

    public function getBasePath(mixed $entity, string $type): string
    {
        $object = $this->propertyMappingFactory->fromField(new $entity(), $type);
        if (is_null($object)) {
            return '';
        }

        return $object->getUriPrefix();
    }

    public function getFullBasePath(mixed $entity, string $type): string
    {
        $basePath = $this->getBasePath($entity, $type);

        return $this->parameterBag->get('kernel.project_dir') . '/public' . $basePath;
    }

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
}
