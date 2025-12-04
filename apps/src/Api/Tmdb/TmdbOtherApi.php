<?php

namespace Labstag\Api\Tmdb;

use Symfony\Contracts\Cache\ItemInterface;

/**
 * TMDB Company API Client
 * Handles company/production company related endpoints.
 */
class TmdbOtherApi extends AbstractTmdbApi
{
    /**
     * Find content by external ID (IMDB).
     *
     * @param string      $externalId     External ID (e.g., IMDB ID)
     * @param string|null $language       Language (e.g., 'en-US', 'fr-FR')
     * @param string      $externalSource External source ('imdb_id', 'freebase_mid', etc.)
     *
     * @return array<string, mixed>|null
     */
    public function findByImdb(string $externalId, ?string $language = null, string $externalSource = 'imdb_id'): ?array
    {
        if ('' === trim($externalId)) {
            return null;
        }

        $params = array_filter([
                'external_source' => $externalSource,
                'language'        => $language ?? 'en-US',
            ]);

        $query    = $this->buildQueryParams($params);
        $cacheKey = 'tmdb_find_' . $externalId . '_' . md5($query);

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($externalId, $query): ?array {
                $url  = self::BASE_URL . '/find/' . $externalId . $query;
                $data = $this->makeRequest($url);

                if (null === $data) {
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
     * Get company details by ID.
     *
     * @param string $companyId Company ID
     *
     * @return array<string, mixed>|null
     */
    public function getCompanyDetails(string $companyId): ?array
    {
        if ('' === trim($companyId)) {
            return null;
        }

        $cacheKey = 'tmdb_company_details_' . $companyId;

        return $this->getCached(
            $cacheKey,
            function (ItemInterface $item) use ($companyId): ?array {
                $url  = self::BASE_URL . '/company/' . $companyId;
                $data = $this->makeRequest($url);

                if (null === $data || empty($data['name'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(604800);
                // 7 days cache (company details rarely change)

                return $data;
            },
            60
        );
    }
}
