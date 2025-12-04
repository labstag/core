<?php

namespace Labstag\Api\Tmdb;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * TMDB Movies API Client
 * Handles all movie-related endpoints.
 */
class TmdbMoviesApi extends AbstractTmdbApi
{
    /**
     * Discover movies with filters.
     *
     * @param array<string, mixed> $filters  Optional filters (genre, year, etc.)
     * @param string|null          $language Language (e.g., 'en-US', 'fr-FR')
     * @param string|null          $region   Region (ISO 3166-1 code)
     * @param int                  $page     Page number
     *
     * @return array<string, mixed>|null
     */
    public function discover(
        array $filters = [],
        ?string $language = null,
        ?string $region = null,
        int $page = 1,
    ): ?array
    {
        $params = array_merge(
            $filters,
            array_filter(
                [
                    'language' => $language ?? 'en-US',
                    'region'   => $region,
                    'page'     => 1 < $page ? $page : null,
                ]
            )
        );

        $query    = http_build_query($params);
        $cacheKey = 'tmdb_movies_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($query): ?array {
                $url  = self::BASE_URL . '/discover/movie?' . $query;
                $data = $this->makeRequest($url);

                if (null === $data || 0 === count($data['results'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(3600);
                // 1 hour cache

                return $data;
            },
            3600
        );
    }

    /**
     * Get movie details by ID.
     *
     * @param string      $movieId          Movie ID
     * @param string|null $language         Language (e.g., 'en-US', 'fr-FR')
     * @param string|null $appendToResponse Comma-separated list of sub-requests (credits,images,videos,etc.)
     *
     * @return array<string, mixed>|null
     */
    public function getDetails(string $movieId, ?string $language = null, ?string $appendToResponse = null): ?array
    {
        $params = [
            'language'           => $language ?? 'en-US',
            'append_to_response' => $appendToResponse,
        ];

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_movie_details_' . $movieId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId, $query) {
                $url  = self::BASE_URL . '/movie/' . $movieId . $query;
                $data = $this->makeRequest($url);

                if (null === $data || empty($data['title'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            86400
        );
    }

    /**
     * Get movie images.
     *
     * @param string      $movieId              Movie ID
     * @param string|null $includeImageLanguage Comma-separated list of languages (e.g., 'en,fr,null')
     *
     * @return array<string, mixed>|null
     */
    public function getImages(string $movieId, ?string $includeImageLanguage = null): ?array
    {
        $params = array_filter([
                'include_image_language' => $includeImageLanguage,
            ]);

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_movie_images_' . $movieId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId, $query): ?array {
                $url  = self::BASE_URL . '/movie/' . $movieId . '/images' . $query;
                $data = $this->makeRequest($url);

                if (null === $data || (empty($data['backdrops']) && empty($data['posters']))) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            86400
        );
    }

    /**
     * Get movie collection details.
     *
     * @param string      $collectionId Collection ID
     * @param string|null $language     Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getMovieCollection(string $collectionId, ?string $language = null): ?array
    {
        if ('' === trim($collectionId)) {
            return null;
        }

        $params = array_filter([
                'language' => $language ?? 'en-US',
            ]);

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_collection_' . $collectionId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($collectionId, $query) {
                $url  = self::BASE_URL . '/collection/' . $collectionId . $query;
                $data = $this->makeRequest($url);

                if (null === $data || empty($data['name'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            86400
        );
    }

    /**
     * Get movie external IDs.
     *
     * @param string $movieId Movie ID
     *
     * @return array<string, mixed>|null
     */
    public function getMovieExternalIds(string $movieId): ?array
    {
        if ('' === trim($movieId)) {
            return null;
        }

        $cacheKey = 'tmdb_movie_external_ids_' . $movieId;

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId): ?array {
                $url  = self::BASE_URL . '/movie/' . $movieId . '/external_ids';
                $data = $this->makeRequest($url);

                if (null === $data) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (external IDs rarely change)

                return $data;
            },
            60
        );
    }

    /**
     * Get movie recommendations.
     *
     * @param string               $movieId           Movie ID
     * @param array<string, mixed> $additionalFilters Additional query parameters
     *
     * @return array<string, mixed>|null
     */
    public function getMovieRecommendations(string $movieId, array $additionalFilters = []): ?array
    {
        $query    = http_build_query($additionalFilters);
        $cacheKey = 'tmdb_movie_recommendations_' . $movieId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId, $query): ?array {
                $url  = self::BASE_URL . '/movie/' . $movieId . '/recommendations?' . $query;
                $data = $this->makeRequest($url);

                if (null === $data) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            60
        );
    }

    /**
     * Get movie release dates.
     *
     * @param string $movieId Movie ID
     *
     * @return array<string, mixed>|null
     */
    public function getMovieReleasesDates(string $movieId): ?array
    {
        if ('' === trim($movieId)) {
            return null;
        }

        $cacheKey = 'tmdb_movie_release_dates_' . $movieId;

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId) {
                $url  = self::BASE_URL . '/movie/' . $movieId . '/release_dates';
                $data = $this->makeRequest($url);

                if (null === $data || empty($data['results'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            86400
        );
    }

    /**
     * Get similar movies.
     *
     * @param string               $movieId           Movie ID
     * @param array<string, mixed> $additionalFilters Additional query parameters (language, page)
     *
     * @return array<string, mixed>|null
     */
    public function getMovieSimilar(string $movieId, array $additionalFilters = []): ?array
    {
        $query    = http_build_query($additionalFilters);
        $cacheKey = 'tmdb_movie_similar_' . $movieId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId, $query): ?array {
                $url  = self::BASE_URL . '/movie/' . $movieId . '/similar?' . $query;
                $data = $this->makeRequest($url);

                if (null === $data) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            60
        );
    }

    /**
     * Get popular movies.
     *
     * @param int         $page     Page number
     * @param string|null $language Language (e.g., 'en-US', 'fr-FR')
     * @param string|null $region   Region (ISO 3166-1 code)
     *
     * @return array<string, mixed>|null
     */
    public function getPopular(int $page = 1, ?string $language = null, ?string $region = null): ?array
    {
        $params = array_filter(
            [
                'page'     => 1 < $page ? $page : null,
                'language' => $language ?? 'en-US',
                'region'   => $region,
            ]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_popular_movies_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($query): ?array {
                $url  = self::BASE_URL . '/movie/popular' . $query;
                $data = $this->makeRequest($url);

                if (null === $data || 0 === count($data['results'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(3600);
                // 1 hour cache

                return $data;
            },
            3600
        );
    }

    /**
     * Get movie videos/trailers.
     *
     * @param string      $movieId  Movie ID
     * @param string|null $language Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getVideos(string $movieId, ?string $language = null): ?array
    {
        $params = array_filter([
                'language' => $language,
            ]);

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_movie_videos_' . $movieId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($movieId, $query): ?array {
                $url  = self::BASE_URL . '/movie/' . $movieId . '/videos' . $query;
                $data = $this->makeRequest($url);

                if (null === $data || 0 === count($data['results'] ?? [])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $data;
            },
            86400
        );
    }

    /**
     * Search for movies.
     *
     * @param string      $searchQuery        Search query
     * @param int         $page               Page number
     * @param string|null $language           Language (e.g., 'en-US', 'fr-FR')
     * @param string|null $region             Region (ISO 3166-1 code)
     * @param bool|null   $includeAdult       Include adult content
     * @param int|null    $year               Primary release year
     * @param int|null    $primaryReleaseYear Primary release year
     *
     * @return array<string, mixed>|null
     */
    public function search(
        string $searchQuery,
        int $page = 1,
        ?string $language = null,
        ?string $region = null,
        ?bool $includeAdult = null,
        ?int $year = null,
        ?int $primaryReleaseYear = null,
    ): ?array
    {
        if ('' === trim($searchQuery)) {
            return null;
        }

        $params = array_filter(
            [
                'query'                => $searchQuery,
                'page'                 => 1 < $page ? $page : null,
                'language'             => $language,
                'region'               => $region,
                'include_adult'        => null !== $includeAdult ? ($includeAdult ? 'true' : 'false') : null,
                'year'                 => $year,
                'primary_release_year' => $primaryReleaseYear,
            ],
            fn (string|int|null $value): bool => null !== $value
        );

        $params['query'] = $searchQuery;
        // Always include query
        if (1 < $page) {
            $params['page'] = $page;
        }

        $cacheKey = 'tmdb_search_movies_' . md5(serialize($params));

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($params): ?array {
                $url  = self::BASE_URL . '/search/movie?' . http_build_query($params);
                $data = $this->makeRequest($url);

                if (null === $data || 0 === count($data['results'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(1800);
                // 30 minutes cache

                return $data;
            },
            1800
        );
    }
}
