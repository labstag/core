<?php

namespace Labstag\Service;

use Essence\Essence;
use Essence\Media;
use Exception;
use Labstag\Entity\Movie;
use Labstag\Entity\Saga;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieService
{
    private const STATUSOK = 200;

    protected array $category = [];

    protected array $collection = [];

    protected array $country = [];

    protected array $saga = [];

    protected array $updatesaga = [];

    protected array $year = [];

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected MovieRepository $movieRepository,
        protected SagaRepository $sagaRepository,
        protected CategoryRepository $categoryRepository,
        protected string $tmdbapiKey,
    )
    {
    }

    public function getCategoryForForm(): array
    {
        if ([] !== $this->category) {
            return $this->category;
        }

        $data       = $this->categoryRepository->findAllByTypeMovie();
        $categories = [];
        foreach ($data as $category) {
            $categories[$category->getTitle()] = $category->getSlug();
        }

        $this->category = $categories;

        return $categories;
    }

    public function getCountryForForm(): array
    {
        if ([] !== $this->country) {
            return $this->country;
        }

        $data    = $this->movieRepository->findAllUniqueCountries();
        $country = [];
        foreach ($data as $value) {
            $country[$value] = $value;
        }

        $this->country = $country;

        return $country;
    }

    public function getDetails(Movie $movie): array
    {
        $details = [];

        $tmdb = $this->getDetailsTmdb($movie->getImdb());
        if (null !== $tmdb) {
            $details['tmdb'] = $tmdb;
        }

        if (isset($details['tmdb']['movie_results'][0]['id'])) {
            $tmdbmovie = $this->getDetailsTmdbMovie($details['tmdb']['movie_results'][0]['id']);
            if (null !== $tmdbmovie) {
                $details['tmdb'] = array_merge($details['tmdb'], $tmdbmovie);
            }
        }

        if (isset($details['tmdb']['belongs_to_collection']['id'])) {
            $idCollection          = (string) $details['tmdb']['belongs_to_collection']['id'];
            $details['collection'] = $this->getDetailsTmdbCollection($idCollection);
        }

        return $details;
    }

    public function getSagaForForm(): array
    {
        if ([] !== $this->saga) {
            return $this->saga;
        }

        $data  = $this->sagaRepository->findAllByTypeMovie();
        $sagas = [];
        foreach ($data as $saga) {
            $movies = $saga->getMovies();
            if (1 == count($movies)) {
                continue;
            }

            $sagas[$saga->getTitle()] = $saga->getSlug();
        }

        $this->saga = $sagas;

        return $sagas;
    }

    public function getVideo(int $movieId): ?array
    {
        $french = $this->getVideoFR($movieId);
        if (!is_null($french)) {
            return $french;
        }

        return $this->getVideoALL($movieId);
    }

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
        $this->updateImdb($movie);
        $details           = $this->getDetails($movie);
        $statusMovie       = $this->updateMovie($movie, $details);
        $statusSaga        = $this->updateSaga($movie, $details);
        $statusVote        = $this->updateVote($movie, $details);
        $statusImage       = $this->updateImageMovie($movie, $details);
        $statusDescription = $this->updateDescription($movie, $details);
        $statusVideo       = $this->updateTrailer($movie, $details);

        return $statusSaga || $statusMovie || $statusVote || $statusImage || $statusDescription || $statusVideo;
    }

    public function updateDescription(Movie $movie, array $details): bool
    {
        if (!in_array($movie->getDescription(), [null, '', '0'], true)) {
            return false;
        }

        if (!isset($details['movie_results'][0]['overview'])) {
            return false;
        }

        $movie->setDescription($details['movie_results'][0]['overview']);

        return true;
    }

    public function updateImageMovie(Movie $movie, array $details): bool
    {
        $poster = $this->getImgMovie($details);
        if ('' === $poster) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));

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

    public function updateImageSaga(Saga $saga, array $details): bool
    {
        $poster = $this->getImgSaga($details);
        if ('' === $poster) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));

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

    public function updateTrailer(Movie $movie, array $details): bool
    {
        if (!in_array($movie->getTrailer(), [null, '', '0'], true)) {
            return false;
        }

        if (!isset($details['movie_results'][0]['id'])) {
            return false;
        }

        $data = $this->getVideo($details['movie_results'][0]['id']);
        $find = false;
        if (!is_array($data)) {
            return $find;
        }

        foreach ($data as $result) {
            if ('YouTube' == $result['site']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                $movie->setTrailer($url);

                $find = true;

                break;
            }
        }

        return $find;
    }

    private function getDetailsTmdb(string $imdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

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
            return null;
        }

        return json_decode($response->getContent(), true);
    }

    private function getDetailsTmdbCollection(string $tmdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        if (isset($this->collection[$tmdbId])) {
            return $this->collection[$tmdbId];
        }

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
            $this->collection[$tmdbId] = null;

            return $this->collection[$tmdbId];
        }

        $this->collection[$tmdbId] = json_decode($response->getContent(), true);

        return $this->collection[$tmdbId];
    }

    private function getDetailsTmdbMovie(string $tmdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

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
            return null;
        }

        return json_decode($response->getContent(), true);
    }

    private function getImgImdb(string $img): string
    {
        return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $img;
    }

    private function getImgMovie(array $data): string
    {
        if (isset($data['tmdb']['movie_results'][0]['poster_path'])) {
            return $this->getImgImdb($data['tmdb']['movie_results'][0]['poster_path']);
        }

        return '';
    }

    private function getImgSaga(array $data): string
    {
        if (isset($data['collection']['poster_path'])) {
            return $this->getImgImdb($data['collection']['poster_path']);
        }

        return '';
    }

    private function getVideoALL(int $movieId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $url      = 'https://api.themoviedb.org/3/movie/' . $movieId . '/videos';
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
            return null;
        }

        $data     = json_decode($response->getContent(), true);
        $trailers = [];
        foreach ($data['results'] as $result) {
            if ('Trailer' == $result['type']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                if (!$this->isVideo($url)) {
                    continue;
                }

                $trailers[] = $result;
            }
        }

        if ([] === $trailers) {
            return null;
        }

        return $trailers;
    }

    private function getVideoFR(int $movieId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $url      = 'https://api.themoviedb.org/3/movie/' . $movieId . '/videos?language=fr-FR';
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
            return null;
        }

        $data     = json_decode($response->getContent(), true);
        $trailers = [];
        foreach ($data['results'] as $result) {
            if ('Trailer' == $result['type']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                if (!$this->isVideo($url)) {
                    continue;
                }

                $trailers[] = $result;
            }
        }

        if ([] === $trailers) {
            return null;
        }

        return $trailers;
    }

    private function isVideo(string $url): bool
    {
        $essence = new Essence();

        // Load any url:
        $media = $essence->extract(
            $url,
            [
                'maxwidth'  => 800,
                'maxheight' => 600,
            ]
        );

        return $media instanceof Media;
    }

    private function updateImdb(Movie $movie): void
    {
        if (!str_starts_with((string) $movie->getImdb(), 'tt')) {
            $movie->setImdb('tt' . str_pad((string) $movie->getImdb(), 7, '0', STR_PAD_LEFT));
        }
    }

    private function updateMovie(Movie $movie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $movie->setAdult($adult);
        $tagline = (string) $details['tmdb']['tagline'];
        $movie->setCitation($tagline);
        $movie->setTmdb($details['tmdb']['id']);

        return true;
    }

    private function updateSaga(Movie $movie, array $details): bool
    {
        if (!isset($details['collection'])) {
            return false;
        }

        $saga = $movie->getSaga();
        if (!$saga instanceof Saga) {
            return false;
        }

        if (isset($this->updatesaga[$saga->getId()])) {
            return false;
        }

        $this->updateImageSaga($saga, $details);
        $saga->setTmdb($details['collection']['id']);
        $saga->setDescription($details['collection']['overview'] ?? '');

        $this->updatesaga[$saga->getId()] = true;

        return true;
    }

    private function updateVote(Movie $movie, array $details): bool
    {
        if (!isset($details['movie_results'][0]['id'])) {
            return false;
        }

        $voteEverage = (float) $details['movie_results'][0]['vote_average'] ?? 0;
        $voteCount   = (int) $details['movie_results'][0]['vote_count'] ?? 0;

        $movie->setEvaluation($voteEverage);
        $movie->setVotes($voteCount);

        return true;
    }
}
