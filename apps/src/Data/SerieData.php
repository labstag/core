<?php

namespace Labstag\Data;

use Labstag\Entity\Page;
use Labstag\Entity\Serie;
use Labstag\Enum\PageEnum;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Routing\RouterInterface;

class SerieData extends PageData implements DataInterface
{
    #[\Override]
    public function generateSlug(object $entity): string
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(
            [
                'type' => PageEnum::SERIES->value,
            ]
        );

        return parent::generateSlug($page) . '/' . $entity->getSlug();
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugSerie($slug);
    }

    public function getJsonLd(object $entity): object
    {
        $tvSeries = Schema::tvSeries();
        $tvSeries->name($entity->getTitle());

        $img = $this->siteService->asset($entity, 'img', true, true);
        if ('' !== $img) {
            $tvSeries->image($img);
        }

        $genres = [];
        foreach ($entity->getCategories() as $category) {
            $genres[] = $category->getTitle();
        }

        if ([] !== $genres) {
            $tvSeries->genre($genres);
        }

        $description = (string) $entity->getDescription();
        $clean       = trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $tvSeries->description($clean);
        $slug = $this->slugService->forEntity($entity);
        $tvSeries->url(
            $this->router->generate(
                'front',
                ['slug' => $slug],
                RouterInterface::ABSOLUTE_URL
            )
        );
        $tvSeries->numberOfSeasons(count($entity->getSeasons()));
        $seasons = [];
        foreach ($entity->getSeasons() as $season) {
            $seasons[] = $this->getJsonLdSeason($season);
        }
        
        $tvSeries->containsSeason($seasons);

        return $tvSeries;
    }

    #[\Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[\Override]
    public function getTitleMeta(object $entity): string
    {
        return $this->getTitle($entity);
    }

    #[\Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugSerie($slug);

        return $page instanceof Serie;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('serie');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return $this->configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Serie;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Serie;
    }

    #[\Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Serie;
    }

    protected function getEntityBySlugSerie(?string $slug): ?object
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

        if ($page->getType() != PageEnum::SERIES->value) {
            return null;
        }

        return $this->entityManager->getRepository(Serie::class)->findOneBy(
            ['slug' => $slugSecond]
        );
    }

    protected function getJsonLdSeason(object $entity): object
    {
        $tvseason = Schema::tvSeason();
        $tvseason->name($entity->getTitle());
        $tvseason->seasonNumber($entity->getNumber());

        $slug = $this->slugService->forEntity($entity);
        $tvseason->url(
            $this->router->generate(
                'front',
                ['slug' => $slug],
                RouterInterface::ABSOLUTE_URL
            )
        );

        return $tvseason;
    }
}
