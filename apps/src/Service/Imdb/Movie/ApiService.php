<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Movie;

use DateTime;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Repository\MovieRepository;
use Labstag\Service\ConfigurationService;

final class ApiService
{
    public function __construct(
        private TheMovieDbApi $theMovieDbApi,
        private ConfigurationService $configurationService,
        private MovieRepository $movieRepository,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetailsMovie(Movie $movie): array
    {
        return $this->theMovieDbApi->getDetailsMovie($movie);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMovieApi(array $data, int $page = 1): array
    {
        $movies = [];
        $tmdbs  = $this->movieRepository->getAllTmdb();
        $search = '';

        if (isset($data['imdb']) && !empty($data['imdb'])) {
            $results = $this->theMovieDbApi->other()->findByImdb($data['imdb']);
            if (isset($results['movie_results'])) {
                $movies = $results['movie_results'];
            }

            return $this->updateResult($movies, $tmdbs);
        }

        if (isset($data['title'])) {
            $search = $data['title'];
        }

        $locale  = $this->configurationService->getLocaleTmdb();
        $results = $this->theMovieDbApi->movies()->search(searchQuery: $search, page: $page, language: $locale);
        if (isset($results['results'])) {
            $movies = $results['results'];
        }

        return $this->updateResult($movies, $tmdbs);
    }

    /**
     * @param mixed[] $movies
     * @param mixed[] $tmdbs
     *
     * @return array<string, mixed>
     */
    private function updateResult(array $movies, array $tmdbs): array
    {
        foreach ($movies as &$movie) {
            $movie['release_date'] = empty($movie['release_date']) ? null : new DateTime($movie['release_date']);
            $movie['poster_path']  = $this->theMovieDbApi->images()->getPosterUrl($movie['poster_path'] ?? '', 100);
        }

        return array_filter($movies, fn (array $movie): bool => !in_array($movie['id'], $tmdbs));
    }
}
