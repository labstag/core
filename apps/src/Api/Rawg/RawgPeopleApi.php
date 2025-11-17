<?php

namespace Labstag\Api\Rawg;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * RAWG People API Client
 * Handles creators, developers, and publishers endpoints.
 */
class RawgPeopleApi extends AbstractRawgApi
{
    /**
     * @return array<string, mixed>|null
     */
    public function getCreatorDetails(string $creatorId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_creator_' . $creatorId, $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($creatorId, $additionalFilters): ?array {
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
    public function getCreatorRoles(array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_creator_roles', $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
    public function getCreatorsList(array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_creators_list', $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_developer_' . $developerId, $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($developerId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_developers_list', $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
    public function getPublisherDetails(string $publisherId, array $additionalFilters = []): ?array
    {
        $cacheKey = $this->buildCacheKey('rawg_publisher_' . $publisherId, $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($publisherId, $additionalFilters): ?array {
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
        $cacheKey = $this->buildCacheKey('rawg_publishers_list', $additionalFilters);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($additionalFilters): ?array {
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
}
