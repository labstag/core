<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Movie;
use Labstag\Entity\MovieCategory;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;

final class MovieService
{

    /**
     * @var array<string, mixed>
     */
    private array $country = [];

    /**
     * @var array<string, mixed>
     */
    private array $year = [];

    public function __construct(
        private MovieRepository $movieRepository,
        private FileService $fileService,
        private SagaRepository $sagaRepository,
        private CategoryService $categoryService,
        private SagaService $sagaService,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function deleteOldSaga(): void
    {
        $data = $this->sagaRepository->findSagaWithoutMovie();
        foreach ($data as $saga) {
            $total = count($saga->getMovies());
            if (0 !== $total) {
                continue;
            }

            $this->sagaRepository->delete($saga);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCountryForForm(): array
    {
        if ([] !== $this->country) {
            return $this->country;
        }

        $country    = $this->movieRepository->findAllUniqueCountries();

        $this->country = $country;

        return $country;
    }

    /**
     * @return array<string, mixed>
     */
    public function getYearForForm(): array
    {
        if ([] !== $this->year) {
            return $this->year;
        }

        $data = $this->movieRepository->findAllUniqueYear();
        $year = [];
        foreach ($data as $value) {
            $year[$value] = $value;
        }

        $this->year = $year;

        return $year;
    }

    public function update(Movie $movie): bool
    {
        $details  = $this->theMovieDbApi->getDetailsMovie($movie);

        $statuses = [
            $this->updateMovie($movie, $details),
            $this->updateSaga($movie, $details),
            $this->updateCategory($movie, $details),
            $this->updateTrailer($movie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @param array<string, mixed> $details
     */
    private function setCertification(array $details, Movie $movie): void
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

    /**
     * @param array<string, mixed> $details
     */
    private function updateCategory(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        foreach ($movie->getCategories() as $category) {
            $movie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title    = trim((string) $genre['name']);
            $category = $this->categoryService->getType($title, MovieCategory::class);
            $movie->addCategory($category);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageMovie(Movie $movie, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' !== (string) $movie->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $movie, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateMovie(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $movie->setSlug(null);
        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $movie->setAdult($adult);
        $movie->setTitle((string) $details['tmdb']['title']);

        $this->setCertification($details, $movie);

        $tagline = (string) $details['tmdb']['tagline'];
        if ('' !== $tagline && '0' !== $tagline) {
            $movie->setCitation($tagline);
        }

        $overview = (string) $details['tmdb']['overview'];
        if ('' !== $overview && '0' !== $overview) {
            $movie->setDescription($overview);
        }

        $voteEverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $movie->setEvaluation($voteEverage);
        $movie->setVotes($voteCount);

        $movie->setCountries($details['tmdb']['origin_country']);

        $movie->setTmdb($details['tmdb']['id']);

        $movie->setReleaseDate(new DateTime($details['tmdb']['release_date']));
        $movie->setDuration((int) $details['tmdb']['runtime']);

        $this->updateImageMovie($movie, $details);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateSaga(Movie $movie, array $details): bool
    {
        if (is_null($details['collection'])) {
            return false;
        }

        $tmdbId = $details['collection']['id'];
        $saga   = $this->sagaService->getSagaByTmdbId((string) $tmdbId);

        $movie->setSaga($saga);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateTrailer(Movie $movie, array $details): bool
    {
        if (is_null($details['videos']) || !is_array($details['videos'])) {
            return false;
        }

        $find = false;

        foreach ($details['videos']['results'] as $result) {
            if ('YouTube' == $result['site'] && 'Trailer' == $result['type']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                $movie->setTrailer($url);

                $find = true;

                break;
            }
        }

        return $find;
    }
}
