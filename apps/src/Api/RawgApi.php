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
     * Make authenticated HTTP request to TMDB API.
     *
     * @return array<string, mixed>|null
     */
    protected function makeRequest(string $url, array $params): ?array
    {
        if ('' === $this->rawgApiKey) {
            return null;
        }

        $params = array_merge(
            [
                'key' => $this->rawgApiKey,
            ],
            $params
        );
        $query    = http_build_query($params);

        $response = $this->httpClient->request('GET', self::BASE_URL . $url.'?' . $query);

        if (self::STATUSOK !== $response->getStatusCode()) {
            return null;
        }

        return json_decode($response->getContent(), true);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCreatorDetails(string $creatorId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_creator_' . $creatorId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($creatorId, $additionalFilters) {
                $data = $this->makeRequest('/creators/' . $creatorId, $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getCreatorsList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_creators_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/creators', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getDeveloperDetails(string $developerId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_developer_' . $developerId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($developerId, $additionalFilters) {
                $data = $this->makeRequest('/developers/' . $developerId, $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getDevelopersList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_developers_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/developers', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameAchievements(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_achievements_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/achievements', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameDetails(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId, $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameDevelopmentTeam(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_dev_team_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/development-team', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameScreenshots(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_screenshots_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/screenshots', $additionalFilters);
                
                if (null === $data || 0 === count($data['results'])) {
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
    public function getGameSeries(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_series_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/game-series', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGamesList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_games_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/games', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

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
    public function getGameStores(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_stores_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/stores', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameTrailers(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_trailers_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/movies', $additionalFilters);
                
                if (null === $data || 0 === count($data['results'])) {
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
    public function getGenreDetails(string $genreId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_genre_' . $genreId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($genreId, $additionalFilters) {
                $data = $this->makeRequest('/genres/' . $genreId, $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGenresList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_genres_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/genres', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (genres don't change often)

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPlatformDetails(string $platformId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_platform_' . $platformId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($platformId, $additionalFilters) {
                $data = $this->makeRequest('/platforms/' . $platformId, $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPlatformsList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_platforms_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/platforms', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPublisherDetails(string $publisherId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_publisher_' . $publisherId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($publisherId, $additionalFilters) {
                $data = $this->makeRequest('/publishers/' . $publisherId, $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getPublishersList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_publishers_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/publishers', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getStoreDetails(string $storeId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_store_' . $storeId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($storeId, $additionalFilters) {
                $data = $this->makeRequest('/stores/' . $storeId, $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getStoresList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_stores_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/stores', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTagDetails(string $tagId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_tag_' . $tagId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tagId, $additionalFilters) {
                $data = $this->makeRequest('/tags/' . $tagId, $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTagsList(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_tags_list_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/tags', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function searchGames(string $searchQuery, array $additionalFilters = []): ?array
    {
        if ('' === $searchQuery) {
            return null;
        }

        $filters = array_merge(
            [
                'search' => $searchQuery,
            ],
            $additionalFilters
        );

        $cacheKey = 'rawg_search_' . md5(serialize($filters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($filters) {
                $data = $this->makeRequest('/games', $filters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(1800);
                // 30 minutes cache for searches

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCreatorRoles(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_creator_roles_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/creator-roles', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (creator roles don't change often)

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameAdditions(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_additions_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/additions', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameParentGames(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_parent_games_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/parent-games', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameRedditPosts(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_reddit_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/reddit', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(3600);
                // 1 hour cache (reddit posts are dynamic)

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameSuggestedGames(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_suggested_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/suggested', $additionalFilters);
                
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
     * @return array<string, mixed>|null
     */
    public function getGameTwitchStreams(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_twitch_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/twitch', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(1800);
                // 30 minutes cache (streams are very dynamic)

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGameYouTubeVideos(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_game_youtube_' . $gameId . '_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters) {
                $data = $this->makeRequest('/games/' . $gameId . '/youtube', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(3600);
                // 1 hour cache (YouTube videos change frequently)

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getParentPlatforms(array $additionalFilters = []): ?array
    {
        $cacheKey = 'rawg_parent_platforms_' . md5(serialize($additionalFilters));

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters) {
                $data = $this->makeRequest('/platforms/lists/parents', $additionalFilters);
                
                if (null === $data) {
                    $item->expiresAfter(0);
                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (parent platforms don't change often)

                return $data;
            },
            60
        );
    }
}
