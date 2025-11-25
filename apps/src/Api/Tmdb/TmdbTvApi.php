<?php

namespace Labstag\Api\Tmdb;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * TMDB TV Series API Client
 * Handles all TV series related endpoints.
 */
class TmdbTvApi extends AbstractTmdbApi
{
    /**
     * Get TV series details by ID.
     *
     * @param string      $seriesId         TV series ID
     * @param string|null $language         Language (e.g., 'en-US', 'fr-FR')
     * @param string|null $appendToResponse Comma-separated list of sub-requests
     *
     * @return array<string, mixed>|null
     */
    public function getDetails(string $seriesId, ?string $language = null, ?string $appendToResponse = null): ?array
    {
        $params = [
            'language'           => $language ?? 'en-US',
            'append_to_response' => $appendToResponse,
        ];

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_series_details_' . $seriesId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $query) {
                $url  = self::BASE_URL . '/tv/' . $seriesId . $query;
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
     * @param string      $seriesId      TV series ID
     * @param int         $seasonNumber  Season number
     * @param int         $episodeNumber Episode number
     * @param string|null $language      Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getEpisodeDetails(
        string $seriesId,
        int $seasonNumber,
        int $episodeNumber,
        ?string $language = null,
    ): ?array
    {
        $params = array_filter(
            [
                'language' => $language ?? 'fr-FR',
            ]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_episode_' . $seriesId . '_s' . $seasonNumber . 'e' . $episodeNumber . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $seasonNumber, $episodeNumber, $query) {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/season/' . $seasonNumber . '/episode/' . $episodeNumber . $query;
                $data = $this->makeRequest($url);

                if (null === $data || empty($data['name'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(60);
                // 1 minute cache for episodes

                return $data;
            },
            60
        );
    }

    /**
     * Get episode external IDs.
     *
     * @param string $seriesId      TV series ID
     * @param int    $seasonNumber  Season number
     * @param int    $episodeNumber Episode number
     *
     * @return array<string, mixed>|null
     */
    public function getEpisodeExternalIds(string $seriesId, int $seasonNumber, int $episodeNumber): ?array
    {
        $cacheKey = 'tmdb_tv_episode_external_ids_' . $seriesId . '_s' . $seasonNumber . 'e' . $episodeNumber;

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $seasonNumber, $episodeNumber): ?array {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/season/' . $seasonNumber . '/episode/' . $episodeNumber . '/external_ids';
                $data = $this->makeRequest($url);

                if (null === $data) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (external IDs rarely change)

                return $data;
            },
            604800
        );
    }

    /**
     * Get episode images.
     *
     * @param string      $seriesId             TV series ID
     * @param int         $seasonNumber         Season number
     * @param int         $episodeNumber        Episode number
     * @param string|null $includeImageLanguage Comma-separated list of languages
     *
     * @return array<string, mixed>|null
     */
    public function getEpisodeImages(
        string $seriesId,
        int $seasonNumber,
        int $episodeNumber,
        ?string $includeImageLanguage = null,
    ): ?array
    {
        $params = array_filter(
            ['include_image_language' => $includeImageLanguage]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_episode_images_' . $seriesId . '_s' . $seasonNumber . 'e' . $episodeNumber . '_' . md5(
            $query
        );

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $seasonNumber, $episodeNumber, $query): ?array {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/season/' . $seasonNumber . '/episode/' . $episodeNumber . '/images' . $query;
                $data = $this->makeRequest($url);

                if (null === $data || empty($data['stills'])) {
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
     * Get TV series images.
     *
     * @param string      $seriesId             TV series ID
     * @param string|null $includeImageLanguage Comma-separated list of languages
     *
     * @return array<string, mixed>|null
     */
    public function getImages(string $seriesId, ?string $includeImageLanguage = null): ?array
    {
        $params = array_filter(
            ['include_image_language' => $includeImageLanguage]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_images_' . $seriesId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $query): ?array {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/images' . $query;
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
     * Get popular TV series.
     *
     * @param int         $page     Page number
     * @param string|null $language Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getPopular(int $page = 1, ?string $language = null): ?array
    {
        $params = array_filter(
            [
                'page'     => 1 < $page ? $page : null,
                'language' => $language ?? 'en-US',
            ]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_popular_tv_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($query): ?array {
                $url  = self::BASE_URL . '/tv/popular' . $query;
                $data = $this->makeRequest($url);

                if (null === $data || 0 === count($data['results'] ?? [])) {
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
     * Get TV season details.
     *
     * @param string      $seriesId     TV series ID
     * @param int         $seasonNumber Season number
     * @param string|null $language     Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getSeasonDetails(string $seriesId, int $seasonNumber, ?string $language = null): ?array
    {
        $params = [
            'language' => $language ?? 'en-US',
        ];

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_season_' . $seriesId . '_' . $seasonNumber . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $seasonNumber, $query) {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/season/' . $seasonNumber . $query;
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
     * Get TV season videos/trailers.
     *
     * @param string      $seriesId     TV series ID
     * @param int         $seasonNumber Season number
     * @param string|null $language     Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getSeasonVideos(string $seriesId, int $seasonNumber, ?string $language = null): ?array
    {
        $params = array_filter(
            ['language' => $language]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_season_videos_' . $seriesId . '_s' . $seasonNumber . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $seasonNumber, $query): ?array {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/season/' . $seasonNumber . '/videos' . $query;
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
     * Get TV series external IDs.
     *
     * @param string $tvId TV series ID
     *
     * @return array<string, mixed>|null
     */
    public function getTvExternalIds(string $tvId): ?array
    {
        $cacheKey = 'tmdb_tv_external_ids_' . $tvId;

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($tvId): ?array {
                $url  = self::BASE_URL . '/tv/' . $tvId . '/external_ids';
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
     * Get TV series recommendations.
     *
     * @param string               $tvId              TV series ID
     * @param array<string, mixed> $additionalFilters Additional query parameters
     *
     * @return array<string, mixed>|null
     */
    public function getTvRecommendations(string $tvId, array $additionalFilters = []): ?array
    {
        $query    = http_build_query($additionalFilters);
        $cacheKey = 'tmdb_tv_recommendations_' . $tvId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($tvId, $query): ?array {
                $url  = self::BASE_URL . '/tv/' . $tvId . '/recommendations?' . $query;
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
     * Get similar TV series.
     *
     * @param string               $tvId              TV series ID
     * @param array<string, mixed> $additionalFilters Additional query parameters (language, page)
     *
     * @return array<string, mixed>|null
     */
    public function getTvSimilar(string $tvId, array $additionalFilters = []): ?array
    {
        $query    = http_build_query($additionalFilters);
        $cacheKey = 'tmdb_tv_similar_' . $tvId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($tvId, $query): ?array {
                $url  = self::BASE_URL . '/tv/' . $tvId . '/similar?' . $query;
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
     * Get TV series videos/trailers.
     *
     * @param string      $seriesId TV series ID
     * @param string|null $language Language (e.g., 'en-US', 'fr-FR')
     *
     * @return array<string, mixed>|null
     */
    public function getVideos(string $seriesId, ?string $language = null): ?array
    {
        $params = array_filter(
            ['language' => $language]
        );

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_tv_videos_' . $seriesId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($seriesId, $query): ?array {
                $url  = self::BASE_URL . '/tv/' . $seriesId . '/videos' . $query;
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
     * Search for TV series.
     *
     * @param string      $searchQuery      Search query
     * @param int         $page             Page number
     * @param string|null $language         Language (e.g., 'en-US', 'fr-FR')
     * @param int|null    $firstAirDateYear First air date year
     * @param bool|null   $includeAdult     Include adult content
     *
     * @return array<string, mixed>|null
     */
    public function search(
        string $searchQuery,
        int $page = 1,
        ?string $language = null,
        ?int $firstAirDateYear = null,
        ?bool $includeAdult = null,
    ): ?array
    {
        if ('' === trim($searchQuery)) {
            return null;
        }

        $params = array_filter(
            [
                'query'               => $searchQuery,
                'page'                => 1 < $page ? $page : null,
                'language'            => $language ?? 'en-US',
                'first_air_date_year' => $firstAirDateYear,
                'include_adult'       => null !== $includeAdult ? ($includeAdult ? 'true' : 'false') : null,
            ],
            fn (string|int|null $value): bool => null !== $value
        );

        $params['query'] = $searchQuery;
        // Always include query
        if (1 < $page) {
            $params['page'] = $page;
        }

        $cacheKey = 'tmdb_search_tv_' . md5(serialize($params));

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($params): ?array {
                $url  = self::BASE_URL . '/search/tv?' . http_build_query($params);
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
