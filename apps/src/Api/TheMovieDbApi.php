<?php

namespace Labstag\Api;

use DateTime;
use Labstag\Api\Tmdb\TmdbImagesApi;
use Labstag\Api\Tmdb\TmdbMoviesApi;
use Labstag\Api\Tmdb\TmdbOtherApi;
use Labstag\Api\Tmdb\TmdbTvApi;
use Labstag\Entity\Company;
use Labstag\Entity\Episode;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
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

    private TmdbOtherApi $tmdbOtherApi;

    private TmdbTvApi $tmdbTvApi;

    public function __construct(
        private ConfigurationService $configurationService,
        CacheService $cacheService,
        HttpClientInterface $httpClient,
        string $tmdbBearerToken,
    )
    {
        $this->tmdbOtherApi  = new TmdbOtherApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbMoviesApi = new TmdbMoviesApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbImagesApi = new TmdbImagesApi($cacheService, $httpClient, $tmdbBearerToken);
        $this->tmdbTvApi     = new TmdbTvApi($cacheService, $httpClient, $tmdbBearerToken);
    }

    public function getDetailsCompany(Company $company): array
    {
        // $json = $company->getJson();
        // if ($this->isCorrectDate($json)) {
        //     return $json;
        // }

        $details                = [];
        $details['tmdb']        = $this->other()->getCompanyDetails($company->getTmdb() ?? '');
        $date                   = new DateTime();
        $details['json_import'] = $date->format('Y-m-d H:i:s');

        return $details;
    }

    public function getDetailsEpisode(Episode $episode): array
    {
        // $json = $episode->getJson();
        // if ($this->isCorrectDate($json)) {
        //     return $json;
        // }

        $details                = [];
        $date                   = new DateTime();
        $details['json_import'] = $date->format('Y-m-d H:i:s');
        $tmdb                   = $episode->getRefseason()->getRefserie()->getTmdb();
        if (in_array($tmdb, [null, '', '0'], true)) {
            return $details;
        }

        $seasonNumber = $episode->getRefseason()->getNumber();
        $episodeNumber   = $episode->getNumber();
        $locale          = $this->configurationService->getLocaleTmdb();
        $details['tmdb'] = $this->tvserie()->getEpisodeDetails($tmdb, $seasonNumber, $episodeNumber, $locale);

        return $details;
    }

    /**
     * @return mixed[][]|null[]
     */
    public function getDetailsMovie(Movie $movie): array
    {
        // $json = $movie->getJson();
        // if ($this->isCorrectDate($json)) {
        //     return $json;
        // }

        $details                = [];
        $date                   = new DateTime();
        $details['json_import'] = $date->format('Y-m-d H:i:s');
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

        $details['tmdb'] = $this->movies()->getDetails($tmdbId, $locale);
        if (is_null($details['tmdb'])) {
            return $details;
        }

        $locale                   = $this->configurationService->getLocaleTmdb();
        $details['videos']        = $this->getVideosMovie($tmdbId);
        $details['release_dates'] = $this->movies()->getMovieReleasesDates($tmdbId);
        $details['collection']    = $this->movies()->getMovieCollection(
            $details['tmdb']['belongs_to_collection']['id'] ?? '',
            $locale
        );
        $details['recommandations'] = $this->movies()->getMovieRecommendations(
            $tmdbId,
            ['language' => $locale]
        );

        $details['other'] = $this->movies()->getMovieExternalIds($tmdbId);

        return $details;
    }

    public function getDetailsSaga(Saga $saga): array
    {
        // $json = $saga->getJson();
        // if ($this->isCorrectDate($json)) {
        //     return $json;
        // }

        $details                = [];
        $date                   = new DateTime();
        $details['json_import'] = $date->format('Y-m-d H:i:s');
        $locale                 = $this->configurationService->getLocaleTmdb();
        $tmdbId                 = $saga->getTmdb();
        $details['tmdb']        = $this->movies()->getMovieCollection($tmdbId, $locale);

        return $details;
    }

    public function getDetailsSeason(Season $season): array
    {
        // $json = $season->getJson();
        // if ($this->isCorrectDate($json)) {
        //     return $json;
        // }

        $details                = [];
        $date                   = new DateTime();
        $details['json_import'] = $date->format('Y-m-d H:i:s');
        $tmdb                   = $season->getRefserie()->getTmdb();
        if (in_array($tmdb, [null, '', '0'], true)) {
            return $details;
        }

        $numberSeason    = $season->getNumber();
        $locale          = $this->configurationService->getLocaleTmdb();
        $details['tmdb'] = $this->tvserie()->getSeasonDetails($tmdb, $numberSeason, $locale);
        if (is_null($details['tmdb'])) {
            return $details;
        }

        $details['videos'] = $this->getVideosSeason($tmdb, $numberSeason);
        $details['other']  = $this->tvserie()->getTvExternalIds($tmdb);

        return $details;
    }

    /**
     * @return mixed[][]|null[]
     */
    public function getDetailsSerie(Serie $serie): array
    {
        // $json = $serie->getJson();
        // if ($this->isCorrectDate($json)) {
        //     return $json;
        // }

        $details                = [];
        $date                   = new DateTime();
        $details['json_import'] = $date->format('Y-m-d H:i:s');
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

        $details['tmdb'] = $this->tvserie()->getDetails($tmdbId, $locale);
        if (is_null($details['tmdb'])) {
            return $details;
        }

        $details['videos']          = $this->getVideosSerie($tmdbId);
        $details['recommandations'] = $this->tvserie()->getTvRecommendations(
            $tmdbId,
            ['language' => $locale]
        );

        return $details;
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

    private function getVideosMovie(string $tmdbId): ?array
    {
        $locale = $this->configurationService->getLocaleTmdb();
        $videos = $this->movies()->getVideos($tmdbId, $locale);
        if (is_null($videos)) {
            return $this->movies()->getVideos($tmdbId);
        }

        return $videos;
    }

    private function getVideosSeason(string $tmdbId, int $numberSeason): ?array
    {
        $locale = $this->configurationService->getLocaleTmdb();
        $videos = $this->tvserie()->getSeasonVideos($tmdbId, $numberSeason, $locale);
        if (is_null($videos)) {
            return $this->tvserie()->getSeasonVideos($tmdbId, $numberSeason);
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

    private function isCorrectDate(?array $json): bool
    {
        if (!isset($json['tmdb']) || is_null($json['tmdb'])) {
            return false;
        }

        if (is_array($json) && isset($json['json_import'])) {
            $importDate = new DateTime($json['json_import']);
            $date       = new DateTime();
            $now        = $date;
            $daysDiff   = $now->diff($importDate)->days;

            if (7 > $daysDiff) {
                return true;
            }
        }

        return false;
    }
}
