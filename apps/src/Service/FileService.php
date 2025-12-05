<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Labstag\Message\FileDeleteMessage;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

final class FileService
{
    private const BYTES = 1024;

    public function __construct(
        #[AutowireIterator('labstag.filestorage')]
        private readonly iterable $fileStorages,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
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
            $entities    = $fileStorage->getEntity();
            if (0 === count($entities)) {
                continue;
            }

            foreach ($entities as $entityClass) {
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

    public function getimportCsvFile(string $path, string $delimiter = ','): array
    {
        $csv = new Csv();
        $csv->setDelimiter($delimiter);
        $csv->setSheetIndex(0);

        $spreadsheet = $csv->load($path);
        $worksheet   = $spreadsheet->getActiveSheet();

        return $this->generateJsonCSV($worksheet);
    }

    public function getimportXmlFile(string $path): array
    {
        $xml = simplexml_load_file($path);
        if (false === $xml) {
            throw new Exception('Error loading XML file');
        }

        $dataJson = [];
        foreach ($xml->children() as $item) {
            $row = [];
            foreach ($item->children() as $child) {
                $row[$child->getName()] = (string) $child;
            }

            $dataJson[] = $row;
        }

        return $dataJson;
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

    public function getSizeFormat(int $size): string
    {
        $units     = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
        ];
        $bytes     = (float) $size;
        $unitIndex = 0;
        $maxIndex  = count($units) - 1;

        while (self::BYTES <= $bytes && $unitIndex < $maxIndex) {
            $bytes /= self::BYTES;
            ++$unitIndex;
        }

        if (0 === $unitIndex) {
            return (int) $bytes . ' ' . $units[$unitIndex];
        }

        return number_format($bytes, 2) . ' ' . $units[$unitIndex];
    }

    public function saveFileInAdapter(string $type, string $fileName, $content): null
    {
        $fileSystem = null;
        foreach ($this->fileStorages as $fileStorage) {
            if ($fileStorage->getType() == $type) {
                $fileSystem = $fileStorage->getFilesystem();
            }
        }

        if (is_null($fileSystem)) {
            return null;
        }

        $fileSystem->write($fileName, $content);

        return null;
    }

    public function setUploadedFile(string $filePath, object $entity, string|PropertyPathInterface $type): void
    {
        try {
            // Si c'est une URL, télécharger le fichier localement
            if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                $tempPath = tempnam(sys_get_temp_dir(), 'download_');
                $content  = file_get_contents($filePath);
                if (false === $content) {
                    $this->logger->error('Failed to download file from URL: ' . $filePath);
                    throw new Exception('Failed to download file from URL: ' . $filePath);
                }

                file_put_contents($tempPath, $content);
                $filePath = $tempPath;
            }

            $this->messageBus->dispatch(new FileDeleteMessage($filePath), [new DelayStamp(60_000)]);

            $uploadedFile = new UploadedFile(
                path: $filePath,
                originalName: basename($filePath),
                mimeType: mime_content_type($filePath),
                test: true
            );

            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $propertyAccessor->setValue($entity, $type, $uploadedFile);
        } catch (Exception $exception) {
            $this->logger->error('Error setting uploaded file: ' . $exception->getMessage());
            throw new Exception(
                'Error setting uploaded file: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @return list<array>
     */
    private function generateJsonCSV(Worksheet $worksheet): array
    {
        $dataJson    = [];
        $headers     = [];
        foreach ($worksheet->getRowIterator() as $i => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            if (1 === $i) {
                foreach ($cellIterator as $cell) {
                    $headers[] = trim((string) $cell->getValue());
                }

                continue;
            }

            $columns = [];
            foreach ($cellIterator as $cell) {
                $columns[] = trim((string) $cell->getValue());
            }

            $dataJson[] = array_combine($headers, $columns);
        }

        return $dataJson;
    }

    /**
     * @return EntityRepository<object>
     */
    private function getRepository(string $entity): EntityRepository
    {
        $entityRepository = $this->entityManager->getRepository($entity);
        if (is_null($entityRepository)) {
            throw new Exception('Repository not found');
        }

        return $entityRepository;
    }
}
