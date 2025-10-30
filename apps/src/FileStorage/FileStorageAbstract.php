<?php

namespace Labstag\FileStorage;

use Exception;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

#[AutoconfigureTag('labstag.filestorage')]
abstract class FileStorageAbstract
{

    protected mixed $adapter = null;

    protected string $type;

    public function __construct(
        protected KernelInterface $kernel,
    )
    {
    }

    /**
     * @param mixed[] $files
     */
    public function deleteFilesByType(array $files): void
    {
        $filesystem       = $this->getFilesystem();
        $directoryListing = $filesystem->listContents('');
        foreach ($directoryListing as $content) {
            if (in_array($content->path(), $files)) {
                $filesystem->delete($content->path());
            }
        }
    }

    public function getEntity(): ?string
    {
        return null;
    }

    /**
     * @return mixed[]
     */
    public function getFilesByDirectory($filesystem, string $directory): array
    {
        $files            = [];
        $directoryListing = $filesystem->listContents($directory);
        foreach ($directoryListing as $content) {
            if ($content->isFile()) {
                $files[] = [
                    'filesystem' => $filesystem,
                    'content'    => $content,
                    'folder'     => $this->getFolder(),
                    'path'       => $content->path(),
                ];

                continue;
            }

            $files = array_merge($files, $this->getFilesByDirectory($filesystem, $content->path()));
        }

        return $files;
    }

    public function getFilesystem(): mixed
    {
        return new Filesystem(
            $this->adapter,
            [
                'public_url' => $this->getFolder(),
            ]
        );
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setAdapter(mixed $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    protected function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    private function getFolder(): mixed
    {
        $config = Yaml::parse(file_get_contents($this->kernel->getProjectDir() . '/config/packages/flysystem.yaml'));

        $storages = $config['flysystem']['storages'];
        if (!array_key_exists($this->type . '.storage', $storages)) {
            throw new Exception('Type not found');
        }

        $storage = $storages[$this->type . '.storage'];

        return $storage['options']['directory'];
    }
}
