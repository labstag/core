<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use DateTime;
use Labstag\Entity\Serie;
use Labstag\Service\VideoService;

final class DetailService
{
    public function __construct(
        private VideoService $videoService,
    )
    {
    }

    public function setCertification(Serie $serie, array $details): void
    {
        if (!isset($details['release_dates']['results']) || 0 === count($details['release_dates']['results'])) {
            return;
        }

        foreach ($details['release_dates']['results'] as $result) {
            if ('FR' != $result['iso_3166_1']) {
                continue;
            }

            foreach ($result['release_dates'] as $release) {
                if ('' === (string) $release['certification']) {
                    continue;
                }

                $serie->setCertification((string) $release['certification']);

                return;
            }
        }
    }

    public function setCitation(Serie $serie, array $details): bool
    {
        $tagline = (string) $details['tmdb']['tagline'];
        if ('' !== $tagline && '0' !== $tagline) {
            $serie->setCitation($tagline);
        }

        return true;
    }

    public function setDescription(Serie $serie, array $details): bool
    {
        $overview = (string) $details['tmdb']['overview'];
        if ('' !== $overview && '0' !== $overview) {
            $serie->setDescription($overview);
        }

        return true;
    }

    public function setLastreleaseDate(Serie $serie, array $details): bool
    {
        $lastReleaseDate = (is_null($details['tmdb']['last_air_date']) || empty($details['tmdb']['last_air_date'])) ? null : new DateTime($details['tmdb']['last_air_date']);
        $serie->setLastreleaseDate($lastReleaseDate);

        return true;
    }

    public function setReleaseDate(Serie $serie, array $details): bool
    {
        $releaseDate = (is_null($details['tmdb']['first_air_date']) || empty($details['tmdb']['first_air_date'])) ? null : new DateTime($details['tmdb']['first_air_date']);
        $serie->setReleaseDate($releaseDate);

        return true;
    }

    public function updateOther(Serie $serie, array $details): bool
    {
        if (!isset($details['other']) || is_null($details['other'])) {
            return false;
        }

        $serie->setImdb((string) $details['other']['imdb_id']);

        return true;
    }

    public function updateSerie(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $serie->setInProduction((bool) $details['tmdb']['in_production']);
        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $serie->setAdult($adult);
        $serie->setTitle((string) $details['tmdb']['name']);

        $voteAverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $serie->setEvaluation($voteAverage);
        $serie->setVotes($voteCount);

        $serie->setCountries($details['tmdb']['origin_country']);

        $serie->setTmdb($details['tmdb']['id']);

        return true;
    }

    public function updateTrailer(Serie $serie, array $details): bool
    {
        $find  = false;
        $video = $this->videoService->getTrailer($details['videos']);
        if (!is_null($video)) {
            $serie->setTrailer($video);
            $find = true;
        }

        return $find;
    }
}
