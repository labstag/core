<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\Recommendation;
use Labstag\Entity\Serie;

class RecommendationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function setRecommendations(object $object, array $recommendations): null
    {
        $entityRepository          = $this->entityManager->getRepository(Movie::class);
        $serieRepository           = $this->entityManager->getRepository(Serie::class);
        $recommendationRepository  = $this->entityManager->getRepository(Recommendation::class);
        foreach ($recommendations as $row) {
            $date = ($row['first_air_date'] ?? $row['release_date']) ?? '';
            if ('' === $date) {
                return null;
            }

            $date = new DateTime($date);
            if ($date > new DateTime()) {
                return null;
            }

            $search = [
                'tmdb' => $row['id'],
            ];
            $field          = ($object instanceof Movie) ? 'refmovie' : (($object instanceof Serie) ? 'refserie' : 'refsaga');
            $recommendation = $recommendationRepository->findOneBy($search);
            $entity         = match ($field) {
                'refmovie' => $entityRepository->findOneBy(
                    [
                        'tmdb' => $row['id'],
                    ]
                ),
                'refserie' => $serieRepository->findOneBy(
                    [
                        'tmdb' => $row['id'],
                    ]
                ),
                'refsaga'  => $entityRepository->findOneBy(
                    [
                        'tmdb' => $row['id'],
                    ]
                ),
            };

            if ($recommendation instanceof Recommendation) {
                continue;
            }

            if (is_object($entity)) {
                continue;
            }

            $recommendation = new Recommendation();
            match ($field) {
                'refmovie' => $recommendation->setRefmovie($object),
                'refserie' => $recommendation->setRefserie($object),
                'refsaga'  => $recommendation->setRefsaga($object),
            };

            $recommendation->setTmdb($row['id']);
            $recommendation->setTitle($row['title'] ?? $row['name']);
            $recommendation->setReleaseDate($date);
            $recommendation->setOverview($row['overview']);
            $recommendation->setPoster($this->theMovieDbApi->images()->getPosterUrl($row['poster_path'] ?? ''));

            $recommendationRepository->save($recommendation);
        }

        return null;
    }
}
