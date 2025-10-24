<?php

namespace Labstag\Data;

interface DataInterface
{
    public function generateSlug(object $entity): string;

    public function getEntity(string $slug): object;

    public function getTitle(object $entity): string;

    public function match(string $slug): bool;

    public function supports(object $entity): bool;
}
