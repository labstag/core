<?php

namespace Labstag\Api;

use Labstag\Service\CacheService;
use Labstag\Service\ConfigurationService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApi
{
    private const STATUSOK = 200;

    public function __construct(
        private CacheService $cacheService,
        private HttpClientInterface $httpClient,
        private ConfigurationService $configurationService,
        private string $tmdbapiKey,
    )
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByImdb(string $imdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb_find_' . $imdbId . '_lang_' . $locale;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($imdbId, $locale) {
                $url      = 'https://api.themoviedb.org/3/find/' . $imdbId . '?external_source=imdb_id&language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);

                $item->expiresAfter(86400);

                return $data;
            },
            60
        );
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    public function getDetailsSerie(array $details, string $tmdbId): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb-serie_' . $tmdbId . '_lang_' . $locale;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId, $locale) {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdbId . '?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
        if (null == $data) {
            return $details;
        }

        $details['tmdb'] = $data;

        return $details;
    }

    public function getDetailsSerieBySeason(int $tmdb, int $seasonNumber): ?array
    {
        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb-serie_find_' . $tmdb . '_season_' . $seasonNumber . '_lang_' . $locale;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdb, $seasonNumber, $locale) {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdb . '/season/' . $seasonNumber . '?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);
                if (0 === count($data['episodes'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return $data;
            },
            60
        );
    }

    public function getEpisode(int $tmdb, int $seasonNumber, int $episodeNumber): ?array
    {
        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb-serie_find_' . $tmdb . '_season_' . $seasonNumber . '_episode_' . $episodeNumber . '_lang_' . $locale;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdb, $seasonNumber, $episodeNumber, $locale): ?array {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdb . '/season/' . $seasonNumber . '/episode/' . $episodeNumber . '?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);

                $item->expiresAfter(60);

                return $data;
            },
            60
        );
        if (is_array($data)) {
            return $data;
        }

        $data = $this->getDetailsSerieBySeason($tmdb, $seasonNumber);

        if (is_null($data)) {
            return null;
        }

        $episodes = $data['episodes'];
        foreach ($episodes as $episode) {
            if ($episode['episode_number'] == $episodeNumber) {
                return $episode;
            }
        }

        return null;
    }

    public function getImgw227h127(string $path): string
    {
        return 'https://image.tmdb.org/t/p/w227_and_h127_bestv2' . $path;
    }

    public function getImgw300h450(string $path): string
    {
        return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $path;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMovieCollection(string $tmdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $locale                    = $this->configurationService->getLocaleTmdb();
        $cacheKey                  = 'tmdb-movie_collection_' . $tmdbId . '_lang_' . $locale;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId, $locale) {
                $url      = 'https://api.themoviedb.org/3/collection/' . $tmdbId . '?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getMovieDetails(string $tmdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb_movie_' . $tmdbId . '_lang_' . $locale;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId, $locale) {
                $url      = 'https://api.themoviedb.org/3/movie/' . $tmdbId . '?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getMovieReleasesDates(string $tmdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $cacheKey = 'tmdb_movie-release_dates_' . $tmdbId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/movie/' . $tmdbId . '/release_dates';
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getTrailerMovie(string $tmdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb_movie-trailers_' . $tmdbId . '_lang_' . $locale;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId, $locale) {
                $url      = 'https://api.themoviedb.org/3/movie/' . $tmdbId . '/videos?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    public function getTrailersSerie(array $details, string $tmdbId): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $locale   = $this->configurationService->getLocaleTmdb();
        $cacheKey = 'tmdb-serie-trailers_' . $tmdbId . '_lang_' . $locale;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId, $locale) {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdbId . '/videos?language=' . $locale;
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );

        if (null == $data) {
            return $details;
        }

        $details['trailers'] = $data;

        return $details;
    }
}
