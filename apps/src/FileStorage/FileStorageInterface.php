<?php

namespace Labstag\FileStorage;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('labstag.filestorage')]
interface FileStorageInterface
{
    public function getEntity(): ?string;
}
