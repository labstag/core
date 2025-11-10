<?php

namespace Labstag\Data;

use Labstag\Entity\Episode;
use Labstag\Entity\Season;
use Spatie\SchemaOrg\Schema;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeasonData extends SerieData implements DataInterface
{
    #[\Override]
    public function asset(mixed $entity, string $field): string
    {
        $asset = parent::asset($entity, $field);
        if ('' !== $asset) {
            return $asset;
        }

        return parent::asset($entity->getRefserie(), $field);
    }

    #[\Override]
    public function generateSlug(object $entity): string
    {
        return parent::generateSlug($entity->getRefserie()) . '/' . $entity->getSlug();
    }

    #[\Override]
    public function getEntity(?string $slug): object
    {
        return $this->getEntityBySlugSeason($slug);
    }

    #[\Override]
    public function getJsonLd(object $entity): object
    {
        $schema = $this->getJsonLdSeason($entity);
        $img = $this->siteService->asset($entity, 'img', true, true);
        if ('' !== $img) {

            $schema->image($img);
        }

        $episodes = [];
        foreach ($entity->getEpisodes() as $episode) {
            $episodes[] = $this->getJsonLdEpisode($episode);
        }
        
        $schema->episode($episodes);
        $serie = Schema::tvSeries();
        $serie->name($entity->getRefserie()->getTitle());
        $slug = $this->slugService->forEntity($entity->getRefserie());
        $serie->url(
            $this->router->generate(
                'front',
                ['slug' => $slug],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
        $schema->partOfSeries($serie);
        return $schema;
    }

    protected function getJsonLdEpisode(Episode $entity): object
    {
        $tvepisode = Schema::tvEpisode();
        $tvepisode->name($entity->getTitle());
        $tvepisode->episodeNumber($entity->getNumber());
        if ($entity->getAirDate()) {
            $tvepisode->episodeNumber($entity->getAirDate()->format('Y-m-d'));
        }

        $description = (string) $entity->getOverview();
        $clean       = trim(html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $tvepisode->description($clean);
        $img = $this->siteService->asset($entity, 'img', true, true);
        if ('' !== $img) {
            $tvepisode->image($img);
        }

        return $tvepisode;
    }

    public function getPrefixSeason(): string
    {
        return 'saison-';
    }

    #[\Override]
    public function getTitle(object $entity): string
    {
        return $entity->getTitle();
    }

    #[\Override]
    public function getTitleMeta(object $entity): string
    {
        return parent::getTitle($entity->getRefserie()) . ' - ' . $this->getTitle($entity);
    }

    #[\Override]
    public function match(?string $slug): bool
    {
        $page = $this->getEntityBySlugSeason($slug);

        return $page instanceof Season;
    }

    #[\Override]
    public function placeholder(): string
    {
        $placeholder = $this->globalPlaceholder('season');
        if ('' !== $placeholder) {
            return $placeholder;
        }

        return parent::configPlaceholder();
    }

    #[\Override]
    public function supportsAsset(object $entity): bool
    {
        return $entity instanceof Season;
    }

    #[\Override]
    public function supportsData(object $entity): bool
    {
        return $entity instanceof Season;
    }

    #[\Override]
    public function supportsJsonLd(object $entity): bool
    {
        return $entity instanceof Season;
    }

    protected function getEntityBySlugSeason(?string $slug): ?object
    {
        if (0 === substr_count((string) $slug, '/')) {
            return null;
        }

        $slugSecond = basename((string) $slug);
        $slugFirst  = dirname((string) $slug);
        $season     = $this->entityManager->getRepository(Season::class)->findOneBy(
            ['slug' => $slugSecond]
        );
        if (!$season instanceof Season) {
            return null;
        }

        if (false === parent::match($slugFirst)) {
            return null;
        }

        $serie      = parent::getEntity($slugFirst);
        $slugSecond = str_replace($this->getPrefixSeason(), '', $slugSecond);

        return $this->entityManager->getRepository(Season::class)->findOneBy(
            [
                'refserie' => $serie,
                'number'   => $slugSecond,
            ]
        );
    }
}
