<?php

namespace Labstag\Api;

use Labstag\Api\Tmdb\TmdbImagesApi;
use Labstag\Api\Tmdb\TmdbMoviesApi;
use Labstag\Api\Tmdb\TmdbOtherApi;
use Labstag\Api\Tmdb\TmdbPersonApi;
use Labstag\Api\Tmdb\TmdbTvApi;
use Labstag\Entity\Company;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Person;
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Service\CacheService;
use Labstag\Service\ConfigurationService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TMDB API Client - Using composition for better organization
 * This class acts as a facade to provide a unified interface while delegating
 * functionality to specialized API clients.
 */
class TheMovieDbApi
{

    private FilesystemAdapter $filesystemAdapter;

    private TmdbImagesApi $tmdbImagesApi;

    private TmdbMoviesApi $tmdbMoviesApi;

    private TmdbOtherApi $tmdbOtherApi;

    private TmdbPersonApi $tmdbPersonApi;

    private TmdbTvApi $tmdbTvApi;

    public function __construct(
        private ConfigurationService $configurationService,
        CacheService $cacheService,
        HttpClientInterface $httpClient,
        string $tmdbBearerToken,
    )
    {
        $this->filesystemAdapter         = new FilesystemAdapter(namespace: 'api_cache', defaultLifetime: 0);
        $this->tmdbOtherApi              = new TmdbOtherApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbMoviesApi             = new TmdbMoviesApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbImagesApi             = new TmdbImagesApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbTvApi                 = new TmdbTvApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbPersonApi             = new TmdbPersonApi($cacheService, $httpClient, $tmdbBearerToken);
    }

    public function getDetailPerson(Person $person): ?array
    {
        $json = $this->getJson($person);
        if (0 !== count($json)) {
            return $json;
        }

        $locale          = $this->configurationService->getLocaleTmdb();
        $details['tmdb'] = $this->tmdbPersonApi->getDetails($person->getTmdb(), $locale);

        $this->setJson($person, $details);

        return $details;
    }

    public function getDetailsCompany(Company $company): array
    {
        $json = $this->getJson($company);
        if (0 !== count($json)) {
            return $json;
        }

        $details                = [];
        $details['tmdb']        = $this->other()->getCompanyDetails($company->getTmdb() ?? '');

        $this->setJson($company, $details);

        return $details;
    }

    public function getDetailsEpisode(Episode $episode): array
    {
        $json = $this->getJson($episode);
        if (0 !== count($json)) {
            return $json;
        }

        $details                = [];
        $tmdb                   = $episode->getRefseason()->getRefserie()->getTmdb();
        $seasonNumber = $episode->getRefseason()->getNumber();
        $episodeNumber      = $episode->getNumber();
        $locale             = $this->configurationService->getLocaleTmdb();
        $details['tmdb']    = $this->tvserie()->getEpisodeDetails($tmdb, $seasonNumber, $episodeNumber, $locale);
        $details['other']   = $this->tvserie()->getEpisodeExternalIds($tmdb, $seasonNumber, $episodeNumber);
        $details['images']  = $this->tvserie()->getEpisodeImages($tmdb, $seasonNumber, $episodeNumber, $locale);
        $details['credits'] = $this->tvserie()->getEpisodeCredits($tmdb, $seasonNumber, $episodeNumber, $locale);
        $this->setJson($episode, $details);

        return $details;
    }

    /**
     * @return mixed[][]|null[]
     */
    public function getDetailsMovie(Movie $movie): array
    {
        $json = $this->getJson($movie);
        if (0 !== count($json)) {
            return $json;
        }

        $details                = [];
        $locale                 = $this->configurationService->getLocaleTmdb();
        $tmdbId                 = $movie->getTmdb();
        if (null === $tmdbId || '' === $tmdbId || '0' === $tmdbId) {
            $data = $this->other()->findByImdb($movie->getImdb(), $locale);
            if (null !== $data && isset($data['movie_results'][0]['id'])) {
                $tmdbId = $data['movie_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return $details;
        }

        $details['tmdb']          = $this->movies()->getDetails($tmdbId, $locale);
        $locale                   = $this->configurationService->getLocaleTmdb();
        $details['videos']        = $this->getVideosMovie($tmdbId);
        $details['credits']       = $this->movies()->getCredits($tmdbId, $locale);
        $details['release_dates'] = $this->movies()->getMovieReleasesDates($tmdbId);
        $details['collection']    = $this->movies()->getMovieCollection(
            $details['tmdb']['belongs_to_collection']['id'] ?? '',
            $locale
        );
        $details['recommendations'] = $this->movies()->getMovieRecommendations(
            $tmdbId,
            ['language' => $locale]
        );

        $details['similar'] = $this->movies()->getMovieSimilar(
            $tmdbId,
            ['language' => $locale]
        );

        $details['other']  = $this->movies()->getMovieExternalIds($tmdbId);
        $details['images'] = $this->movies()->getImages($tmdbId, $locale);
        $this->setJson($movie, $details);

        return $details;
    }

    public function getDetailsSaga(Saga $saga): array
    {
        $json = $this->getJson($saga);
        if (0 !== count($json)) {
            return $json;
        }

        $details                = [];
        $locale                 = $this->configurationService->getLocaleTmdb();
        $tmdbId                 = $saga->getTmdb();
        $details['tmdb']        = $this->movies()->getMovieCollection($tmdbId, $locale);
        $this->setJson($saga, $details);

        return $details;
    }

    public function getDetailsSeason(Season $season): array
    {
        $json = $this->getJson($season);
        if (0 !== count($json)) {
            return $json;
        }

        $details                = [];
        $tmdb                   = $season->getRefserie()->getTmdb();
        $numberSeason       = $season->getNumber();
        $locale             = $this->configurationService->getLocaleTmdb();
        $details['tmdb']    = $this->tvserie()->getSeasonDetails($tmdb, $numberSeason, $locale);
        $details['videos']  = $this->getVideosSeason($tmdb, $numberSeason);
        $details['other']   = $this->tvserie()->getTvExternalIds($tmdb);
        $details['credits'] = $this->tvserie()->getSeasonCredits($tmdb, $numberSeason, $locale);
        $this->setJson($season, $details);

        return $details;
    }

    /**
     * @return mixed[][]|null[]
     */
    public function getDetailsSerie(Serie $serie): array
    {
        $json = $this->getJson($serie);
        if (0 !== count($json)) {
            return $json;
        }

        $details                = [];
        $locale                 = $this->configurationService->getLocaleTmdb();
        $tmdbId                 = $serie->getTmdb();
        if (null === $tmdbId || '' === $tmdbId || '0' === $tmdbId) {
            $data = $this->other()->findByImdb($serie->getImdb(), $locale);
            if (null !== $data && isset($data['tv_results'][0]['id'])) {
                $tmdbId = $data['tv_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return $details;
        }

        $details['tmdb']            = $this->tvserie()->getDetails($tmdbId, $locale);
        $details['videos']          = $this->getVideosSerie($tmdbId);
        $details['recommendations'] = $this->tvserie()->getTvRecommendations(
            $tmdbId,
            ['language' => $locale]
        );

        $details['similar'] = $this->tvserie()->getTvSimilar(
            $tmdbId,
            ['language' => $locale]
        );
        $details['credits'] = $this->tvserie()->getCredits($tmdbId, $locale);
        $this->setJson($serie, $details);

        return $details;
    }

    public function getVideosMovie(string $tmdbId): ?array
    {
        $locale = $this->configurationService->getLocaleTmdb();
        $videos = $this->movies()->getVideos($tmdbId, $locale);
        if (is_null($videos)) {
            return $this->movies()->getVideos($tmdbId);
        }

        return $videos;
    }

    public function getVideosSeason(string $tmdbId, int $numberSeason): ?array
    {
        $locale = $this->configurationService->getLocaleTmdb();
        $videos = $this->tvserie()->getSeasonVideos($tmdbId, $numberSeason, $locale);
        if (is_null($videos)) {
            return $this->tvserie()->getSeasonVideos($tmdbId, $numberSeason);
        }

        return $videos;
    }

    /**
     * Get direct access to images API for advanced usage.
     */
    public function images(): TmdbImagesApi
    {
        return $this->tmdbImagesApi;
    }

    /**
     * Get direct access to movies API for advanced usage.
     */
    public function movies(): TmdbMoviesApi
    {
        return $this->tmdbMoviesApi;
    }

    public function other(): TmdbOtherApi
    {
        return $this->tmdbOtherApi;
    }

    /**
     * Get direct access to TV API for advanced usage.
     */
    public function tvserie(): TmdbTvApi
    {
        return $this->tmdbTvApi;
    }

    private function getJson(object $object)
    {
        $cacheKey      = 'api_tmdb_' . $object->getId();
        $cacheItem     = $this->filesystemAdapter->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        return [];
    }

    private function getVideosSerie(string $tmdbId): ?array
    {
        $locale = $this->configurationService->getLocaleTmdb();
        $videos = $this->tvserie()->getVideos($tmdbId, $locale);
        if (is_null($videos)) {
            return $this->tvserie()->getVideos($tmdbId);
        }

        return $videos;
    }

    private function setJson(object $object, array $data, int $ttl = 3600): void
    {
        $cacheKey      = 'api_tmdb_' . $object->getId();
        $cacheItem     = $this->filesystemAdapter->getItem($cacheKey);
        $cacheItem->set($data);
        $cacheItem->expiresAfter($ttl);

        $this->filesystemAdapter->save($cacheItem);
    }
}
