<?php

namespace Labstag\Api\Rawg;

use Labstag\Service\CacheService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Abstract base class for RAWG API clients
 * Provides common functionality shared across different API endpoints.
 */
abstract class AbstractRawgApi
{
    protected const BASE_URL = 'https://api.rawg.io/api';

    protected const STATUSOK = 200;

    public function __construct(
        protected CacheService $cacheService,
        protected HttpClientInterface $httpClient,
        protected string $rawgApiKey,
    )
    {
    }

    /**
     * Helper method to build cache key with filters.
     *
     * @param array<string, mixed> $filters Additional filters to include in cache key
     */
    protected function buildCacheKey(string $baseKey, array $filters = []): string
    {
        if ($filters === []) {
            return $baseKey;
        }

        return $baseKey . '_' . md5(serialize($filters));
    }

    /**
     * Make authenticated HTTP request to RAWG API.
     *
     * @param array<string, mixed> $params Query parameters
     *
     * @return array<string, mixed>|null
     */
    protected function makeRequest(string $url, array $params = []): ?array
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
        $query = http_build_query($params);

        $response = $this->httpClient->request('GET', self::BASE_URL . $url . '?' . $query);

        if (self::STATUSOK !== $response->getStatusCode()) {
            return null;
        }

        return json_decode($response->getContent(), true);
    }
}
