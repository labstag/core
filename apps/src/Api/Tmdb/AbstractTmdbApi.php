<?php

namespace Labstag\Api\Tmdb;

use Labstag\Service\CacheService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Abstract base class for TMDB API clients
 * Provides common functionality shared across different API endpoints.
 */
abstract class AbstractTmdbApi
{
    protected const BASE_URL = 'https://api.themoviedb.org/3';

    protected const STATUSOK = 200;

    public function __construct(
        protected CacheService $cacheService,
        protected HttpClientInterface $httpClient,
        protected string $tmdbBearerToken,
    )
    {
    }

    /**
     * Helper method to build query parameters.
     *
     * @param array<string, mixed> $params Parameters to filter and encode
     *
     * @return string Query string
     */
    protected function buildQueryParams(array $params): string
    {
        $filtered = array_filter($params, fn ($value): bool => null !== $value && '' !== $value);

        return [] === $filtered ? '' : '?' . http_build_query($filtered);
    }

    /**
     * Generic cache wrapper for API requests.
     *
     * @param string   $cacheKey     Cache key
     * @param callable $callback     Function to execute if cache miss
     * @param int      $cacheTimeout Cache timeout in seconds
     *
     * @return array<string, mixed>|null
     */
    protected function getCached(string $cacheKey, callable $callback, int $cacheTimeout = 60): ?array
    {
        return $this->cacheService->get($cacheKey, $callback, $cacheTimeout);
    }

    /**
     * Make authenticated HTTP request to TMDB API.
     *
     * @return array<string, mixed>|null
     */
    protected function makeRequest(string $url): ?array
    {
        if ('' === $this->tmdbBearerToken) {
            return null;
        }

        $response = $this->httpClient->request(
            'GET',
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->tmdbBearerToken,
                    'accept'        => 'application/json',
                ],
            ]
        );

        if (self::STATUSOK !== $response->getStatusCode()) {
            return null;
        }

        return json_decode($response->getContent(), true);
    }
}
