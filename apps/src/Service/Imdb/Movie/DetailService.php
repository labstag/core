<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use Labstag\Entity\Movie;
use Labstag\Service\VideoService;

final class DetailService
{
    public function __construct(
        private VideoService $videoService,
    ) {
    }

    public function updateMovie(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $movie->setInProduction((bool) $details['tmdb']['in_production']);
        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $movie->setAdult($adult);
        $movie->setTitle((string) $details['tmdb']['name']);

        $voteAverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $movie->setEvaluation($voteAverage);
        $movie->setVotes($voteCount);

        $movie->setCountries($details['tmdb']['origin_country']);

        $movie->setTmdb($details['tmdb']['id']);

        return true;
    }

    public function updateOther(Movie $movie, array $details): bool
    {
        if (!isset($details['other']) || is_null($details['other'])) {
            return false;
        }

        $movie->setImdb((string) $details['other']['imdb_id']);

        return true;
    }

    public function setCertification(Movie $movie, array $details): void
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

                $movie->setCertification((string) $release['certification']);

                return;
            }
        }
    }

    public function updateTrailer(Movie $movie, array $details): bool
    {
        $find  = false;
        $video = $this->videoService->getTrailer($details['videos']);
        if (!is_null($video)) {
            $movie->setTrailer($video);
            $find = true;
        }

        return $find;
    }
}
