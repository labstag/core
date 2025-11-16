<?php

namespace Labstag\Api;

use Labstag\Api\Tmdb\TmdbImagesApi;
use Labstag\Api\Tmdb\TmdbMoviesApi;
use Labstag\Api\Tmdb\TmdbTvApi;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Service\CacheService;
use Labstag\Service\ConfigurationService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TMDB API Client - Using composition for better organization
 * This class acts as a facade to provide a unified interface while delegating
 * functionality to specialized API clients.
 */
class TheMovieDbApi
{

    private TmdbImagesApi $tmdbImagesApi;

    private TmdbMoviesApi $tmdbMoviesApi;

    private TmdbTvApi $tmdbTvApi;

    public function __construct(
        private ConfigurationService $configurationService,
        CacheService $cacheService,
        HttpClientInterface $httpClient,
        string $tmdbBearerToken,
    )
    {
        $this->tmdbMoviesApi = new TmdbMoviesApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbImagesApi = new TmdbImagesApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbTvApi     = new TmdbTvApi($cacheService, $httpClient, $tmdbBearerToken);
    }

    // ============ LEGACY/COMPATIBILITY METHODS ============

    /**
     * Find content by IMDB ID (movies, TV shows).
     *
     * @return array<string, mixed>|null
     */
    public function findByImdb(string $imdbId, ?string $language = null): ?array
    {
        // Delegate to movies API which can handle the find endpoint
        return $this->tmdbMoviesApi->findByImdb($imdbId, $language);
    }

    public function getDetailsEpisode(Episode $episode): array
    {
        $details = [];
        $tmdb    = $episode->getRefseason()->getRefserie()->getTmdb();
        if (in_array($tmdb, [null, '', '0'], true)) {
            return [];
        }

        $seasonNumber = $episode->getRefseason()->getNumber();
        if ($seasonNumber === null || $seasonNumber === 0) {
            return [];
        }

        $episodeNumber = $episode->getNumber();
        if ($episodeNumber === null || $episodeNumber === 0) {
            return [];
        }

        $locale          = $this->configurationService->getLocaleTmdb();
        $details['tmdb'] = $this->tvserie()->getEpisodeDetails($tmdb, $seasonNumber, $episodeNumber, $locale);

        return $details;
    }

    /**
     * @return mixed[][]|null[]
     */
    public function getDetailsMovie(Movie $movie): array
    {
        $details = [];
        $locale  = $this->configurationService->getLocaleTmdb();
        $tmdbId  = $movie->getTmdb();
        if (null === $tmdbId || '' === $tmdbId || '0' === $tmdbId) {
            $data = $this->findByImdb($movie->getImdb(), $locale);
            if (null !== $data && isset($data['movie_results'][0]['id'])) {
                $tmdbId = $data['movie_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return [];
        }

        $details['tmdb'] = $this->movies()->getDetails($tmdbId, $locale);

        $details['videos'] = $this->getVideosMovie($tmdbId);

        $details['release_dates'] = $this->movies()->getMovieReleasesDates($tmdbId);

        $details['collection'] = $this->movies()->getMovieCollection(
            $details['tmdb']['belongs_to_collection']['id'] ?? '',
            $locale
        );

        return $details;
    }

    public function getDetailsSeason(Season $season): array
    {
        $details = [];
        $tmdb    = $season->getRefserie()->getTmdb();
        if (in_array($tmdb, [null, '', '0'], true)) {
            return [];
        }

        $numberSeason    = $season->getNumber();
        $locale          = $this->configurationService->getLocaleTmdb();
        $details['tmdb'] = $this->tvserie()->getSeasonDetails($tmdb, $numberSeason, $locale);
        if (is_null($details['tmdb'])) {
            return [];
        }

        return $details;
    }

    /**
     * @return mixed[][]|null[]
     */
    public function getDetailsSerie(Serie $serie): array
    {
        $details = [];
        $locale  = $this->configurationService->getLocaleTmdb();
        $tmdbId  = $serie->getTmdb();
        if (null === $tmdbId || '' === $tmdbId || '0' === $tmdbId) {
            $data = $this->findByImdb($serie->getImdb(), $locale);
            if (null !== $data && isset($data['tv_results'][0]['id'])) {
                $tmdbId = $data['tv_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return [];
        }

        $details['tmdb'] = $this->tvserie()->getDetails($tmdbId, $locale);

        $details['videos'] = $this->getVideosSerie($tmdbId);

        return $details;
    }

    /**
     * Get direct access to images API for advanced usage.
     */
    public function images(): TmdbImagesApi
    {
        return $this->tmdbImagesApi;
    }

    // ============ DIRECT API ACCESS ============

    /**
     * Get direct access to movies API for advanced usage.
     */
    public function movies(): TmdbMoviesApi
    {
        return $this->tmdbMoviesApi;
    }

    /**
     * Get direct access to TV API for advanced usage.
     */
    public function tvserie(): TmdbTvApi
    {
        return $this->tmdbTvApi;
    }

    private function getVideosMovie(string $tmdbId): ?array
    {
        $locale = $this->configurationService->getLocaleTmdb();
        $videos = $this->movies()->getVideos($tmdbId, $locale);
        if (is_null($videos)) {
            return $this->movies()->getVideos($tmdbId);
        }

        return $videos;
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
}
