<?php

namespace Labstag\Data;

use Symfony\Component\HttpFoundation\Response;

interface DataInterface
{
    public function asset(mixed $entity, string $field): string;

    public function generateSlug(object $entity): array;

    public function getEntity(?string $slug): object;

    public function getTitle(object $entity): string;

    public function match(?string $slug): bool;

    public function placeholder(): string;

    public function scriptBefore(object $entity, Response $response): Response;

    public function supportsAsset(object $entity): bool;

    public function supportsData(object $entity): bool;

    public function supportsJsonLd(object $entity): bool;

    public function supportsScriptBefore(object $entity): bool;

    public function supportsShortcode(string $className): bool;
}
