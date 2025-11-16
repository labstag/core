<?php

namespace Labstag\Api\Rawg;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * RAWG Metadata API Client
 * Handles genres, platforms, stores, tags, and other metadata endpoints.
 */
class RawgMetadataApi extends AbstractRawgApi
{
    /**
     * @return array<string, mixed>|null
     */
    public function getGenreDetails(string $genreId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_genre_' . $genreId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($genreId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_genres_list', $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
    public function getParentPlatforms(array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_parent_platforms', $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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

    /**
     * @return array<string, mixed>|null
     */
    public function getPlatformDetails(string $platformId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_platform_' . $platformId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($platformId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_platforms_list', $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
    public function getStoreDetails(string $storeId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_store_' . $storeId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($storeId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_stores_list', $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_tag_' . $tagId, $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tagId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_tags_list', $additionalFilters);

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
}
