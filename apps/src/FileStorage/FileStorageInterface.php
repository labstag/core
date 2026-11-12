<?php

declare(strict_types=1);

namespace Labstag\FileStorage;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('labstag.filestorage')]
interface FileStorageInterface
{
    public function getEntity(): array;
}
