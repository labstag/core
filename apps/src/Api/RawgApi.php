<?php

namespace Labstag\Api;

use Labstag\Api\Rawg\RawgGamesApi;
use Labstag\Api\Rawg\RawgMetadataApi;
use Labstag\Api\Rawg\RawgPeopleApi;
use Labstag\Service\CacheService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * RAWG API Client Facade
 * Provides unified access to all RAWG API functionality through composition.
 */
class RawgApi
{

    private RawgGamesApi $rawgGamesApi;

    private RawgMetadataApi $rawgMetadataApi;

    private RawgPeopleApi $rawgPeopleApi;

    public function __construct(
        CacheService $cacheService,
        HttpClientInterface $httpClient,
        string $rawgApiKey,
    )
    {
        $this->rawgGamesApi    = new RawgGamesApi($cacheService, $httpClient, $rawgApiKey);
        $this->rawgPeopleApi   = new RawgPeopleApi($cacheService, $httpClient, $rawgApiKey);
        $this->rawgMetadataApi = new RawgMetadataApi($cacheService, $httpClient, $rawgApiKey);
    }

    public function games(): RawgGamesApi
    {
        return $this->rawgGamesApi;
    }

    public function metadata(): RawgMetadataApi
    {
        return $this->rawgMetadataApi;
    }

    public function people(): RawgPeopleApi
    {
        return $this->rawgPeopleApi;
    }
}
