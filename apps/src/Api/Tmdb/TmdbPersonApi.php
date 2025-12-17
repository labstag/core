<?php

namespace Labstag\Api\Tmdb;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * TMDB Person API Client
 * Handles all person related endpoints.
 */
class TmdbPersonApi extends AbstractTmdbApi
{
    /**
     * Get person details by ID.
     *
     * @param string      $personId         Person ID
     * @param string|null $language         Language (e.g., 'en-US', 'fr-FR')
     * @param string|null $appendToResponse Comma-separated list of sub-requests (combined_credits,images,etc.)
     *
     * @return array<string, mixed>|null
     */
    public function getDetails(string $personId, ?string $language = null, ?string $appendToResponse = null): ?array
    {
        if ('' === trim($personId)) {
            return null;
        }

        $params = [
            'language'           => $language ?? 'fr-FR',
            'append_to_response' => $appendToResponse,
        ];

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_person_details_' . $personId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($personId, $query): ?array {
                $url  = self::BASE_URL . '/person/' . $personId . $query;
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
}
