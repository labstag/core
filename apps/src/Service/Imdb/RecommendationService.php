<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\Recommendation;
use Labstag\Entity\Serie;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\RecommendationRepository;
use Labstag\Repository\SerieRepository;

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
        $entityRepository           = $this->entityManager->getRepository(Movie::class);
        $serieRepository            = $this->entityManager->getRepository(Serie::class);
        $recommendationRepository   = $this->entityManager->getRepository(Recommendation::class);
        foreach ($recommendations as $recommendation) {
            $field          = ($object instanceof Movie) ? 'refmovie' : (($object instanceof Serie) ? 'refserie' : 'refsaga');
            $date           = ($recommendation['first_air_date'] ?? $recommendation['release_date']) ?? '';
            if ('' === $date) {
                continue;
            }

            $date = new DateTime($date);
            if ($date > new DateTime()) {
                continue;
            }

            $this->setRecommendation(
                $field,
                $date,
                $object,
                $serieRepository,
                $entityRepository,
                $recommendationRepository,
                $recommendation
            );
        }

        return null;
    }

    private function getEntity(
        string $field,
        array $row,
        RecommendationRepository $recommendationRepository,
        SerieRepository $serieRepository,
        MovieRepository $movieRepository,
    ): ?object
    {
        $search = [
            'tmdb' => trim((string) $row['id']),
        ];
        $recommendation = $recommendationRepository->findOneBy($search);
        if ($recommendation instanceof Recommendation) {
            return $recommendation;
        }

        return match ($field) {
            'refmovie' => $movieRepository->findOneBy(
                [
                    'tmdb' => trim((string) $row['id']),
                ]
            ),
            'refserie' => $serieRepository->findOneBy(
                [
                    'tmdb' => trim((string) $row['id']),
                ]
            ),
            'refsaga'  => $movieRepository->findOneBy(
                [
                    'tmdb' => trim((string) $row['id']),
                ]
            ),
        };
    }

    private function setRecommendation(
        string $field,
        DateTime $date,
        object $object,
        SerieRepository $serieRepository,
        MovieRepository $movieRepository,
        RecommendationRepository $recommendationRepository,
        array $row,
    ): void
    {
        $entity = $this->getEntity($field, $row, $recommendationRepository, $serieRepository, $movieRepository);

        if (is_object($entity)) {
            return;
        }

        $recommendation = new Recommendation();
        match ($field) {
            'refmovie' => $recommendation->setRefmovie($object),
            'refserie' => $recommendation->setRefserie($object),
            'refsaga'  => $recommendation->setRefsaga($object),
        };

        $recommendation->setTmdb(trim((string) $row['id']));
        $recommendation->setTitle($row['title'] ?? $row['name']);
        $recommendation->setReleaseDate($date);
        $recommendation->setOverview($row['overview']);
        $recommendation->setPoster($this->theMovieDbApi->images()->getPosterUrl($row['poster_path'] ?? ''));

        $recommendationRepository->save($recommendation);
    }
}
