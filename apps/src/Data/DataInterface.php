<?php

namespace Labstag\Data;

interface DataInterface
{
    public function asset(mixed $entity, string $field): string;

    public function generateSlug(object $entity): string;

    public function getEntity(string $slug): object;

    public function getTitle(object $entity): string;

    public function match(string $slug): bool;

    public function placeholder(): string;

    public function supportsAsset(object $entity): bool;

    public function supportsData(object $entity): bool;

    public function supportsShortcode(string $className): bool;
}
