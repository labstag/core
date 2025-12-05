<?php

namespace Labstag\Data;

use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Enum\PageEnum;
use Override;

class MovieData extends SagaData implements DataInterface
{
    #[Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = $this->fileService->asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        if ('backdrop' === $field) {
            return $this->fileService->asset($entity, 'poster');
        }

        return $this->fileService->asset($entity, $field);
    }

    #[Override]
    public function generateSlug(object $entity): array
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::MOVIES->value,
            ]
        );

        $slug = parent::generateSlugPage($page);
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugMovie($slug);
    }

    #[Override]
    public function getJsonLd(object $entity): object
    {
        return $this->getJsonLdMovie($entity);
    }

    #[Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugMovie($slug);

        return $page instanceof Movie;
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('movie');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Movie;
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Movie;
    }

    #[Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Movie;
    }

    protected function getEntityBySlugMovie(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);

        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            ['slug' => $slugFirst]
        );
        if (!$page instanceof Page) {
            return null;
        }

        if ($page->getType() != PageEnum::MOVIES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Movie::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
