<?php

namespace Labstag\Api;

use Labstag\Service\CacheService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * IGDB API Client (Internet Game Database)
 * API documentation: https://api-docs.igdb.com/
 * Uses Twitch OAuth for authentication.
 */
class IgdbApi
{
    private const BASE_URL = 'https://api.igdb.com/v4';

    private const TOKEN_URL = 'https://id.twitch.tv/oauth2/token';

    private ?string $accessToken = null;

    public function __construct(
        private CacheService $cacheService,
        private HttpClientInterface $httpClient,
        private string $igdbClientId,
        private string $igdbClientSecret,
    )
    {
    }

    /**
     * Build image URL from IGDB image hash.
     *
     * @param string $imageId Image ID/hash
     * @param string $size    Size (thumb, cover_small, screenshot_med, screenshot_big, cover_big, logo_med, 720p, 1080p)
     */
    public function buildImageUrl(string $imageId, string $size = 'cover_big'): string
    {
        return sprintf('https://images.igdb.com/igdb/image/upload/t_%s/%s.jpg', $size, $imageId);
    }

    /**
     * Get game details by ID.
     *
     * @param int $gameId Game ID
     *
     * @return array<string, mixed>|null
     */
    public function getGameDetails(int $gameId): ?array
    {
        $cacheKey = 'igdb_game_' . $gameId;

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($gameId): ?array {
                $body   = sprintf('fields *; where id = %d;', $gameId);
                $result = $this->makeRequest('games', $body);

                if (null === $result || [] === $result) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $result[0] ?? null;
            },
            86400
        );
    }

    /**
     * Search for games.
     *
     * @param string $query  Search query
     * @param int    $limit  Number of results (max 500)
     * @param int    $offset Offset for pagination
     *
     * @return array<string, mixed>|null
     */
    public function searchGames(string $query, int $limit = 10, int $offset = 0): ?array
    {
        if ('' === trim($query)) {
            return null;
        }

        $body = sprintf('search "%s"; fields *; limit %d; offset %d;', $query, min($limit, 500), $offset);

        return $this->makeRequest('games', $body);
    }

    public function setBody(
        string $search = '',
        array $fields = [],
        array $where = [],
        int $limit = 10,
        int $offset = 0,
    ): string
    {
        $body = [];
        if ('' !== $search && '0' !== $search) {
            $body[] = sprintf('search "%s"', $search);
        }

        $body[] = ([] === $fields) ? 'fields *' : 'fields ' . implode(',', $fields);
        if ([] !== $where) {
            $body[] = 'where ' . implode(' & ', $where);
        }

        $body[] = 'limit ' . min($limit, 500);
        if (0 !== $offset) {
            $body[] = 'offset ' . $offset;
        }

        return implode(';', $body) . ';';
    }

    public function setUrl(string $url, string $body): mixed
    {
        $cacheKey = 'igdb_' . $url . '_' . md5($body);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($url, $body): ?array {
                $result = $this->makeRequest($url, $body);

                if (null === $result || [] === $result) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);
                // 24 hours cache

                return $result ?? null;
            },
            86400
        );
    }

    /**
     * Get access token from Twitch OAuth.
     */
    private function getAccessToken(): ?string
    {
        if (null !== $this->accessToken) {
            return $this->accessToken;
        }

        $cacheKey = 'igdb_access_token';

        $this->accessToken = $this->getCached(
            $cacheKey,
            function (ItemInterface $item): ?string {
                try {
                    $response = $this->httpClient->request(
                        'POST',
                        self::TOKEN_URL,
                        [
                            'query' => [
                                'client_id'     => $this->igdbClientId,
                                'client_secret' => $this->igdbClientSecret,
                                'grant_type'    => 'client_credentials',
                            ],
                        ]
                    );

                    if (Response::HTTP_OK !== $response->getStatusCode()) {
                        $item->expiresAfter(0);

                        return null;
                    }

                    $data = $response->toArray();

                    if (!isset($data['access_token'], $data['expires_in'])) {
                        $item->expiresAfter(0);

                        return null;
                    }

                    // Cache token until it expires (minus 5 minutes for safety)
                    $item->expiresAfter($data['expires_in'] - 300);

                    return $data['access_token'];
                } catch (TransportExceptionInterface $transportException) {
                    $item->expiresAfter(0);
                    unset($transportException);

                    return null;
                }
            },
            3600
        );

        return $this->accessToken;
    }

    /**
     * Get cached data or execute callback.
     *
     * @param string   $key      Cache key
     * @param callable $callback Callback to execute if cache miss
     * @param int      $ttl      Time to live in seconds
     */
    private function getCached(string $key, callable $callback, int $ttl = 3600): mixed
    {
        return $this->cacheService->get($key, $callback, $ttl);
    }

    /**
     * Make a request to IGDB API using Apicalypse query language.
     *
     * @param string $endpoint API endpoint
     * @param string $body     Apicalypse query body
     *
     * @return array<string, mixed>|null
     */
    private function makeRequest(string $endpoint, string $body): ?array
    {
        $token = $this->getAccessToken();
        if (null === $token) {
            return null;
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                self::BASE_URL . '/' . $endpoint,
                [
                    'headers' => [
                        'Client-ID'     => $this->igdbClientId,
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'body'    => $body,
                ]
            );

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                return null;
            }

            return $response->toArray();
        } catch (TransportExceptionInterface $transportException) {
            unset($transportException);

            return null;
        }
    }
}
