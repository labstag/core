<?php

namespace Labstag\Data;

interface DataInterface
{
    public function match(string $slug): bool;

    public function getEntity(string $slug): object;

    public function supports(object $entity): bool;

    public function generateSlug(object $entity): string;

    public function getTitle(object $entity): string;
}