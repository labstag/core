<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\Category;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MovieService
{
    private const STATUSOK = 200;

    /**
     * @var array<string, mixed>
     */
    private array $category = [];

    /**
     * @var array<string, mixed>
     */
    private array $collection = [];

    /**
     * @var array<string, mixed>
     */
    private array $country = [];

    /**
     * @var array<string, mixed>
     */
    private array $genres = [];

    /**
     * @var array<string, mixed>
     */
    private array $sagaForm = [];

    /**
     * @var array<string, mixed>
     */
    private array $sagas = [];

    /**
     * @var array<string, mixed>
     */
    private array $updatesaga = [];

    /**
     * @var array<string, mixed>
     */
    private array $year = [];

    public function __construct(
        private CacheService $cacheService,
        private HttpClientInterface $httpClient,
        private MovieRepository $movieRepository,
        private SagaRepository $sagaRepository,
        private CategoryRepository $categoryRepository,
        private string $tmdbapiKey,
    )
    {
    }

    public function deleteOldCategory(): void
    {
        $data = $this->categoryRepository->findAllByTypeMovieWithoutMovie();
        foreach ($data as $category) {
            $total = count($category->getMovies());
            if (0 !== $total) {
                continue;
            }

            $this->categoryRepository->delete($category);
        }
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
    public function getCategoryForForm(): array
    {
        if ([] !== $this->category) {
            return $this->category;
        }

        $data       = $this->categoryRepository->findAllByTypeMovieEnable();
        $categories = [];
        foreach ($data as $category) {
            $categories[$category->getTitle()] = $category->getSlug();
        }

        $this->category = $categories;

        return $categories;
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
     * @return array<string, mixed>|null
     */
    public function getDetailsTmdb(string $imdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $cacheKey = 'tmdb_movie_find_' . $imdbId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($imdbId) {
                $url      = 'https://api.themoviedb.org/3/find/' . $imdbId . '?external_source=imdb_id&language=fr-FR';
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $data = json_decode($response->getContent(), true);
                if (0 === count($data['movie_results'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return $data;
            },
            60
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getSagaForForm(): array
    {
        if ([] !== $this->sagaForm) {
            return $this->sagaForm;
        }

        $data  = $this->sagaRepository->findAllByTypeMovieEnable();
        $sagas = [];
        foreach ($data as $saga) {
            $movies = $saga->getMovies();
            if (1 === count($movies)) {
                continue;
            }

            $sagas[$saga->getTitle()] = $saga->getSlug();
        }

        $this->sagaForm = $sagas;

        return $sagas;
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
        $details  = $this->getDetails($movie);
        $statuses = [
            $this->updateMovie($movie, $details),
            $this->updateSaga($movie, $details),
            $this->updateCategory($movie, $details),
            $this->updateTrailer($movie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function getDetails(Movie $movie): array
    {
        $details = [];

        $tmdbId = $movie->getTmdb();
        if (null === $tmdbId || '' === $tmdbId || '0' === $tmdbId) {
            $data   = $this->getDetailsTmdb($movie->getImdb());
            if (null !== $data && isset($data['movie_results'][0]['id'])) {
                $tmdbId = $data['movie_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return [];
        }

        $details = $this->getDetailsReleasesDates($details, $tmdbId);
        $details = $this->getDetailsTmdbMovie($details, $tmdbId);
        $details = $this->getTrailersTmdbMovie($details, $tmdbId);

        return $this->getDetailsTmdbCollection($details);
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    private function getDetailsReleasesDates(
        array $details,
        string $tmdbId,
    ): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $cacheKey = 'tmdb_movie-release_dates_' . $tmdbId;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/movie/' . $tmdbId . '/release_dates';
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
        if (null == $data) {
            return $details;
        }

        $details['release_dates'] = $data;

        return $details;
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    private function getDetailsTmdbCollection(
        array $details,
    ): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        if (!isset($details['tmdb']['belongs_to_collection'])) {
            return $details;
        }

        $tmdbId = $details['tmdb']['belongs_to_collection']['id'];

        if (isset($this->collection[$tmdbId])) {
            $details['collection'] = $this->collection[$tmdbId];

            return $details;
        }

        $cacheKey                  = 'tmdb-movie_collection_' . $tmdbId;
        $this->collection[$tmdbId] = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/collection/' . $tmdbId . '?language=fr-FR';
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );

        $details['collection'] = $this->collection[$tmdbId];

        return $details;
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    private function getDetailsTmdbMovie(
        array $details,
        string $tmdbId,
    ): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $cacheKey = 'tmdb_movie_' . $tmdbId;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/movie/' . $tmdbId . '?language=fr-FR';
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
        if (null == $data) {
            return $details;
        }

        $details['tmdb'] = $data;

        return $details;
    }

    private function getImgImdb(string $img): string
    {
        return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $img;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgMovie(array $data): string
    {
        if (isset($data['tmdb']['poster_path'])) {
            return $this->getImgImdb($data['tmdb']['poster_path']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgSaga(array $data): string
    {
        if (isset($data['collection']['poster_path'])) {
            return $this->getImgImdb($data['collection']['poster_path']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    private function getTrailersTmdbMovie(
        array $details,
        string $tmdbId,
    ): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $cacheKey = 'tmdb_movie-trailers_' . $tmdbId;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/movie/' . $tmdbId . '/videos?language=fr-FR';
                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->tmdbapiKey,
                            'accept'        => 'application/json',
                        ],
                    ]
                );
                if (self::STATUSOK !== $response->getStatusCode()) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );

        if (null == $data) {
            return $details;
        }

        $details['trailers'] = $data;

        return $details;
    }

    /**
     * @return array<string, mixed>
     */
    private function initGenres(): array
    {
        if ([] !== $this->genres) {
            return $this->genres;
        }

        $data       = $this->categoryRepository->findAllByTypeMovie();
        $categories = [];
        foreach ($data as $category) {
            $title              = trim((string) $category->getTitle());
            $categories[$title] = $category;
        }

        $this->genres = $categories;

        return $categories;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function setCertification(
        array $details,
        Movie $movie,
    ): void
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
    private function updateCategory(
        Movie $movie,
        array $details,
    ): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        $this->initGenres();

        foreach ($movie->getCategories() as $category) {
            $movie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title = trim((string) $genre['name']);
            if (isset($this->genres[$title])) {
                $category = $this->genres[$title];
                $movie->addCategory($category);
                continue;
            }

            $category = new Category();
            $category->setTitle($title);
            $category->setType('movie');
            $this->categoryRepository->save($category);
            $this->genres[$title] = $category;

            $movie->addCategory($category);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageMovie(
        Movie $movie,
        array $details,
    ): bool
    {
        $poster = $this->getImgMovie($details);
        if ('' === $poster) {
            return false;
        }

        if ('' != $movie->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents(
                $tempPath,
                file_get_contents($poster)
            );

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename($tempPath),
                mimeType: mime_content_type($tempPath),
                test: true
            );

            $movie->setImgFile($uploadedFile);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageSaga(
        Saga $saga,
        array $details,
    ): bool
    {
        $poster = $this->getImgSaga($details);
        if ('' === $poster) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents(
                $tempPath,
                file_get_contents($poster)
            );

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename($tempPath),
                mimeType: mime_content_type($tempPath),
                test: true
            );

            $saga->setImgFile($uploadedFile);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateMovie(
        Movie $movie,
        array $details,
    ): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

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
    private function updateSaga(
        Movie $movie,
        array $details,
    ): bool
    {
        if (!isset($details['collection'])) {
            return false;
        }

        $tmdbId = $details['collection']['id'];

        if (!isset($this->sagas[$tmdbId])) {
            $saga = $this->sagaRepository->findOneBy(
                ['tmdb' => $tmdbId]
            );
            if (!$saga instanceof Saga) {
                $saga = new Saga();
                $saga->setTitle($details['collection']['name']);
                $saga->setTmdb($tmdbId);
                $this->sagaRepository->save($saga);
            }

            $this->sagas[$tmdbId] = $saga;
        }

        $saga = $this->sagas[$tmdbId];
        if (!isset($this->updatesaga[$tmdbId])) {
            $saga->setTitle($details['collection']['name']);
            $saga->setDescription($details['collection']['overview'] ?? '');

            $this->updateImageSaga($saga, $details);
            $this->sagaRepository->save($saga);

            $this->updatesaga[$saga->getId()] = true;
        }

        $movie->setSaga($saga);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateTrailer(
        Movie $movie,
        array $details,
    ): bool
    {
        if (!isset($details['trailers'])) {
            return false;
        }

        $find = false;

        foreach ($details['trailers']['results'] as $result) {
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
