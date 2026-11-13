<?php

declare(strict_types=1);

namespace Labstag\Service\File;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class FileCleanupService
{
    public function __construct(
        #[AutowireIterator('labstag.filestorage')]
        private readonly iterable $fileStorages,
        private EntityManagerInterface $entityManager,
    ) {
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

    public function deletedFileByEntities(): void
    {
        $filters = $this->entityManager->getFilters();
        if ($filters->has('softdeleteable') && $filters->isEnabled('softdeleteable')) {
            $filters->disable('softdeleteable');
        }

        foreach ($this->fileStorages as $fileStorage) {
            $deletes  = [];
            $entities = $fileStorage->getEntity();
            if (0 === count($entities)) {
                continue;
            }

            $files = $fileStorage->getFilesByDirectory($fileStorage->getFilesystem(), '');
            foreach ($files as $file) {
                $find = $this->findInEntities($entities, $file['path']);
                if (!$find) {
                    $deletes[] = $file['path'];
                }
            }

            $fileStorage->deleteFilesByType($deletes);
        }

        if ($filters->has('softdeleteable') && !$filters->isEnabled('softdeleteable')) {
            $filters->enable('softdeleteable');
        }
    }

    private function findInEntities(array $entities, string $file): bool
    {
        foreach ($entities as $entity) {
            $find = $this->findInEntity($entity, $file);
            if ($find) {
                return true;
            }
        }

        return false;
    }

    private function findInEntity(string $entityClass, string $file): bool
    {
        $repository = $this->entityManager->getRepository($entityClass);
        $fields     = $this->findInFields($repository, ['img', 'image', 'file']);

        foreach ($fields as $field) {
            $results = $repository->findBy([$field => $file]);
            if (count($results) > 0) {
                return true;
            }
        }

        return false;
    }

    private function findInFields(EntityRepository $entityRepository, array $fields): array
    {
        $metadata = $entityRepository->getClassMetadata();
        $found    = [];

        foreach ($fields as $field) {
            if ($metadata->hasField($field) || $metadata->hasAssociation($field)) {
                $found[] = $field;
            }
        }

        return $found;
    }
}
