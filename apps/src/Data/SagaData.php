<?php

namespace Labstag\Data;

use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Saga;
use Labstag\Enum\PageEnum;
use Override;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Routing\RouterInterface;

class SagaData extends PageData implements DataInterface
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

    public function getDefaultImage(object $entity): ?string
    {
        return $entity->getPoster();
    }

    #[Override]
    public function generateSlug(object $entity): array
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::MOVIES->value,
            ]
        );

        $slug = parent::generateSlug($page);
        $slug['slug'] .= '/' . $entity->getSlug();

        return $slug;
    }

    #[Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugSaga($slug);
    }

    public function getJsonLd(object $entity): object
    {
        $movieSeries = $this->getJsonLdSaga($entity);
        $description = (string) $entity->getDescription();
        $clean       = trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $movieSeries->description($clean);

        $img = $this->siteService->asset($entity, 'backdrop', true, true);
        if ('' !== $img) {
            $movieSeries->image($img);
        }

        $movies = [];
        foreach ($entity->getMovies() as $movieEntity) {
            if ($movieEntity->isEnable()) {
                $movies[] = $this->getJsonLdMovie($movieEntity);
            }
        }

        $movieSeries->hasPart($movies);

        return $movieSeries;
    }

    public function getJsonLdMovie(Movie $entity): object
    {
        $movie = Schema::movie();
        $movie->name($entity->getTitle());

        $img = $this->siteService->asset($entity, 'backdrop', true, true);
        if ('' !== $img) {
            $movie->image($img);
        }

        return $movie;
    }

    public function getJsonLdSaga(Saga $saga): object
    {
        $movieSeries = Schema::movieSeries();
        $movieSeries->name($saga->getTitle());

        $params = $this->slugService->forEntity($saga);
        $movieSeries->url($this->router->generate('front', $params, RouterInterface::ABSOLUTE_URL));

        return $movieSeries;
    }

    #[Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[Override]
    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    #[Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugSaga($slug);

        return $page instanceof Saga;
    }

    #[Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('saga');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    #[Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    #[Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Saga;
    }

    protected function getEntityBySlugSaga(?string $slug): ?object
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

        return $this->entityManager->getRepository(Saga::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }
}
