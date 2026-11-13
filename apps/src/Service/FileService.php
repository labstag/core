<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Essence\Essence;
use Essence\Media;
use Exception;
use GdImage;
use Labstag\Message\FileDeleteMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Labstag\Service\File\FileCleanupService;
use Labstag\Service\File\FileImportService;
use Labstag\Service\File\FileStorageService;
use Labstag\Service\File\FileUploadService;

final class FileService
{
    private const BYTES = 1024;

    public function __construct(
        #[AutowireIterator('labstag.filestorage')]
        private readonly iterable $fileStorages,
        private FileStorageService $fileStorageService,
        private FileCleanupService $fileCleanupService,
        private FileImportService $fileImportService,
        private FileUploadService $fileUploadService,
        private MessageDispatcherService $messageDispatcherService,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private PropertyMappingFactory $propertyMappingFactory,
    )
    {
    }

    public function asset(mixed $entity, string $field): string
    {
        return $this->fileUploadService->asset($entity, $field);
    }

    public function deleteAll(): void
    {
        $this->fileCleanupService->deleteAll();
    }

    public function deletedFileByEntities(): void
    {
        $this->fileCleanupService->deletedFileByEntities();
    }

    public function getBasePath(mixed $entity, string $type): string
    {
        return $this->fileStorageService->getBasePath($entity, $type);
    }

    public function getFileInAdapter(string $type, string $fileName): ?string
    {
        return $this->fileStorageService->getFileInAdapter($type, $fileName);
    }

    /**
     * @return mixed[]
     */
    public function getFiles(): array
    {
        return $this->fileStorageService->getFiles();
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilesByAdapter(string $type, string $file = ''): array
    {
        return $this->fileStorageService->getFilesByAdapter($type, $file);
    }

    public function getFullBasePath(mixed $entity, string $type): string
    {
        return $this->fileStorageService->getFullBasePath($entity, $type);
    }

    public function getimportCsvFile(string $path, string $delimiter = ','): array
    {
        return $this->fileImportService->getimportCsvFile($path, $delimiter);
    }

    public function getimportXmlFile(string $path): array
    {
        return $this->fileImportService->getimportXmlFile($path);
    }

    public function getInfoImage(string $file): array
    {
        return $this->fileUploadService->getInfoImage($file);
    }

    /**
     * @param mixed[]|object $entity
     *
     * @return mixed[]
     */
    public function getMappingForEntity(object|array $entity): array
    {
        return $this->fileUploadService->getMappingForEntity($entity);
    }

    public function getMediaByUrl(?string $url): ?Media
    {
        return $this->fileUploadService->getMediaByUrl($url);
    }

    public function getSizeFormat(int $size): string
    {
        return $this->fileUploadService->getSizeFormat($size);
    }

    public function saveFileInAdapter(string $type, string $fileName, $content): null
    {
        $this->fileStorageService->saveFileInAdapter($type, $fileName, $content);

        return null;
    }

    public function setImgPatchwork(array $files): ?string
    {
        return $this->fileUploadService->setImgPatchwork($files);
    }

    public function setUploadedFile(string $filePath, object $entity, string|PropertyPathInterface $type): void
    {
        $this->fileUploadService->setUploadedFile($filePath, $entity, $type);
    }

    /**
     * @return list<array>
     */
    /**
     * @return EntityRepository<object>
     */
    private function saveCanvas(GdImage $gdImage, int $jpegQuality): ?string
    {
        $output = tempnam(sys_get_temp_dir(), 'poster_grid_');
        if (false === $output) {
            return null;
        }

        $success = imagejpeg($gdImage, $output, $jpegQuality);

        return $success ? $output : null;
    }
}
