<?php

declare(strict_types=1);

namespace Labstag\Service\File;

use Essence\Essence;
use Essence\Media;
use Exception;
use GdImage;
use Labstag\Message\FileDeleteMessage;
use Labstag\Service\MessageDispatcherService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

final class FileUploadService
{
    private const BYTES = 1024;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private PropertyMappingFactory $propertyMappingFactory,
        private LoggerInterface $logger,
        private FileStorageService $fileStorageService,
        private MessageDispatcherService $messageDispatcherService,
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

            $basePath = $this->fileStorageService->getBasePath($entity, $mapping->getFilePropertyName());
            $content  = $propertyAccessor->getValue($entity, $mapping->getFileNamePropertyName());
            if ('' != $content) {
                $file = $basePath . '/' . $content;
            }
        }

        return $file;
    }

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

    public function getMappingForEntity(object|array $entity): array
    {
        return $this->propertyMappingFactory->fromObject($entity);
    }

    public function getMediaByUrl(?string $url): ?Media
    {
        if (is_null($url) || '' === $url || '0' === $url) {
            return null;
        }

        $essence = new Essence();

        // Load any url:
        $media = $essence->extract(
            $url,
            [
                'maxwidth'  => 800,
                'maxheight' => 600,
            ]
        );
        if (!$media instanceof Media) {
            return null;
        }

        return $media;
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

    public function setImgPatchwork(array $files): ?string
    {
        if ([] === $files) {
            return null;
        }

        $cellSize    = 300;
        $jpegQuality = 90;
        $files       = $this->fillGridFiles($files);
        $count       = count($files);
        $cols        = (int) ceil(sqrt($count));

        $final = $this->createCanvas($files, $cellSize, $cols);
        if (is_null($final)) {
            return null;
        }

        $this->placeImagesOnCanvas($final, $files, $cellSize, $cols);

        return $this->saveCanvas($final, $jpegQuality);
    }

    public function setUploadedFile(string $filePath, object $entity, string|PropertyPathInterface $type): void
    {
        if ('' === $filePath) {
            return;
        }

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

            $this->messageDispatcherService->dispatch(new FileDeleteMessage($filePath), [new DelayStamp(60_000)]);

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

    private function createCanvas(array $files, int $cellSize, int $cols): ?GdImage
    {
        $count       = count($files);
        $rows        = (int) ceil($count / $cols);
        $finalWidth  = $cols * $cellSize;
        $finalHeight = $rows * $cellSize;
        $final       = imagecreatetruecolor($finalWidth, $finalHeight);

        if (false === $final) {
            return null;
        }

        $white = imagecolorallocate($final, 255, 255, 255);
        if (false === $white) {
            return null;
        }

        imagefill($final, 0, 0, $white);

        return $final;
    }

    private function fillGridFiles(array $files): array
    {
        $count = count($files);
        $cols  = (int) ceil(sqrt($count));
        $rows  = (int) ceil($count / $cols);

        if ($rows * $cols > $count) {
            $rest = $rows * $cols - $count;
            for ($i = 0; $i < $rest; ++$i) {
                $files[] = $files[$i % $count];
            }
        }

        return $files;
    }

    private function loadImageFromFile(string $file): GdImage|false
    {
        $imgInfo = @getimagesize($file);
        if (false === $imgInfo || !isset($imgInfo['mime'])) {
            return false;
        }

        return match ($imgInfo['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($file),
            'image/png'  => @imagecreatefrompng($file),
            'image/gif'  => @imagecreatefromgif($file),
            default      => false,
        };
    }

    private function placeImagesOnCanvas(GdImage $gdImage, array $files, int $cellSize, int $cols): void
    {
        $i = 0;
        foreach ($files as $file) {
            $src = $this->loadImageFromFile($file);
            if (false === $src) {
                continue;
            }

            $x = ($i % $cols) * $cellSize;
            $y = (int) floor($i / $cols) * $cellSize;

            imagecopyresampled($gdImage, $src, $x, $y, 0, 0, $cellSize, $cellSize, imagesx($src), imagesy($src));

            ++$i;
        }
    }

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
