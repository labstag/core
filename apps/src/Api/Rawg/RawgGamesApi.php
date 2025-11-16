<?php

namespace Labstag\Api\Rawg;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * RAWG Games API Client
 * Handles all game-related endpoints and their associated data.
 */
class RawgGamesApi extends AbstractRawgApi
{
    /**
     * @return array<string, mixed>|null
     */
    public function getGameAchievements(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_achievements_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameAdditions(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_additions_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameDetails(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_game_dev_team_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameParentGames(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_parent_games_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_game_reddit_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameScreenshots(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_screenshots_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_game_series_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_games', $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_game_stores_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameSuggestedGames(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_suggested_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameTrailers(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_trailers_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function getGameTwitchStreams(string $gameId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_game_twitch_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_game_youtube_' . $gameId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($gameId, $additionalFilters): ?array {
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
    public function searchGames(string $searchQuery, array $additionalFilters = []): ?array
    {
        if ('' === $searchQuery) {
            return null;
        }

        $filters = array_merge(
            ['search' => $searchQuery],
            $additionalFilters
        );

        $cacheKey = $this->buildCacheKey('rawg_search', $filters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($filters): ?array {
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
}
