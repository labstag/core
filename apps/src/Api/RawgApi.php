<?php

namespace Labstag\Api;

use Labstag\Service\CacheService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RawgApi
{
    private const BASE_URL = 'https://api.rawg.io/api';

    private const STATUSOK = 200;

    public function __construct(
        private CacheService $cacheService,
        private HttpClientInterface $httpClient,
        private string $rawgApiKey,
    )
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCreatorDetails(string $creatorId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_creator_' . $creatorId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($creatorId) {
                $url      = self::BASE_URL . '/creators/' . $creatorId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCreatorsList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_creators_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/creators?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDeveloperDetails(string $developerId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_developer_' . $developerId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($developerId) {
                $url      = self::BASE_URL . '/developers/' . $developerId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDevelopersList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_developers_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/developers?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameAchievements(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_achievements_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '/achievements?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameDetails(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameDevelopmentTeam(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_dev_team_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '/development-team?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameScreenshots(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_screenshots_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '/screenshots?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);
                if (0 === count($data['results'])) {
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
     * @return array<string, mixed>|null
     */
    public function getGameSeries(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_series_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '/game-series?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGamesList(array $filters = []): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $query = http_build_query(
            array_merge(
                [
                    'key' => $this->rawgApiKey,
                ],
                $filters
            )
        );
        $cacheKey = 'rawg_games_' . md5($query);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($query) {
                $url      = self::BASE_URL . '/games?' . $query;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);

                $item->expiresAfter(3600);
                // 1 hour cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameStores(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_stores_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '/stores?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameTrailers(string $gameId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_game_trailers_' . $gameId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId) {
                $url      = self::BASE_URL . '/games/' . $gameId . '/movies?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);
                if (0 === count($data['results'])) {
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
     * @return array<string, mixed>|null
     */
    public function getGenreDetails(string $genreId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_genre_' . $genreId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($genreId) {
                $url      = self::BASE_URL . '/genres/' . $genreId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGenresList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_genres_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/genres?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (genres don't change often)

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPlatformDetails(string $platformId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_platform_' . $platformId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($platformId) {
                $url      = self::BASE_URL . '/platforms/' . $platformId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPlatformsList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_platforms_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/platforms?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPublisherDetails(string $publisherId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_publisher_' . $publisherId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($publisherId) {
                $url      = self::BASE_URL . '/publishers/' . $publisherId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPublishersList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_publishers_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/publishers?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getStoreDetails(string $storeId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_store_' . $storeId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($storeId) {
                $url      = self::BASE_URL . '/stores/' . $storeId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getStoresList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_stores_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/stores?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTagDetails(string $tagId): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_tag_' . $tagId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tagId) {
                $url      = self::BASE_URL . '/tags/' . $tagId . '?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTagsList(): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $cacheKey = 'rawg_tags_list';

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) {
                $url      = self::BASE_URL . '/tags?key=' . $this->rawgApiKey;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return json_decode($response->getContent(), true);
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchGames(string $searchQuery, array $additionalFilters = []): ?array
    {
        if ('' === $this->rawgApiKey || '' === $searchQuery) {
            return null;
        }

        $filters = array_merge(
            [
                'search' => $searchQuery,
                'key'    => $this->rawgApiKey,
            ],
            $additionalFilters
        );

        $query    = http_build_query($filters);
        $cacheKey = 'rawg_search_' . md5($query);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($query) {
                $url      = self::BASE_URL . '/games?' . $query;
                $response = $this->httpClient->request('GET', $url);

                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);

                $item->expiresAfter(1800);
                // 30 minutes cache for searches

                return $data;
            },
            60
        );
    }
}
