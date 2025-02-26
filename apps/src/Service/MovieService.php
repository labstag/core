<?php

namespace Labstag\Service;

use Exception;
use Labstag\Entity\Movie;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\MovieRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieService
{
    private const STATUSOK = 200;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected MovieRepository $movieRepository,
        protected CategoryRepository $categoryRepository,
        protected string $omdbapiKey,
        protected string $tmdbapiKey,
    )
    {
    }

    public function getCountryForForm(): array
    {
        $data = $this->movieRepository->findAllUniqueCountries();
        $country = [];
        foreach ($data as $value) {
            $country[$value] = $value;
        }

        return $country;
    }

    public function getYearForForm(): array
    {
        $data = $this->movieRepository->findAllUniqueYear();
        $year = [];
        foreach ($data as $value) {
            $year[$value] = $value;
        }

        return $year;
    }

    public function getCategoryForForm(): array
    {
        $data = $this->categoryRepository->findBy(
            [
                'type' => 'movie'
            ],
            [
                'title' => 'ASC'
            ]
        );
        $categories = [];
        foreach ($data as $category) {
            $categories[$category->getTitle()] = $category->getSlug();
        }

        return $categories;
    }

    public function getDetails(string $imdbId): array
    {
        $details = [];
        $omdb    = $this->getDetailsOmDBAPI($imdbId);
        if (null !== $omdb) {
            $details = array_merge($details, $omdb);
        }

        $tmdb = $this->getDetailsTmdb($imdbId);
        if (null !== $tmdb) {
            $details = array_merge($details, $tmdb);
        }

        return $details;
    }

    public function update(Movie $movie): bool
    {
        $statusImage       = $this->updateImage($movie);
        $statusDescription = $this->updateDescription($movie);

        return $statusImage || $statusDescription;
    }

    public function updateDescription(Movie $movie): bool
    {
        if (!in_array($movie->getDescription(), [null, '', '0'], true)) {
            return false;
        }

        $tmdb = $this->getDetailsTmdb($movie->getImdb());
        if (!isset($tmdb['movie_results'][0]['overview'])) {
            return false;
        }

        $movie->setDescription($tmdb['movie_results'][0]['overview']);

        return true;
    }

    public function updateImage(Movie $movie): bool
    {
        $details = $this->getDetails($movie->getImdb());
        $poster  = $this->getImg($details);
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

    private function getDetailsOmDBAPI(string $imdbId): ?array
    {
        if ('' === $this->omdbapiKey) {
            return null;
        }

        $url      = 'http://www.omdbapi.com/?i=tt' . $imdbId . '&apikey=' . $this->omdbapiKey;
        $response = $this->httpClient->request('GET', $url);
        if (self::STATUSOK !== $response->getStatusCode()) {
            return null;
        }

        return json_decode($response->getContent(), true);
    }

    private function getDetailsTmdb(string $imdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $url      = 'https://api.themoviedb.org/3/find/tt' . $imdbId . '?external_source=imdb_id&language=fr-FR';
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

    private function getImg(array $data): string
    {
        if (isset($data['movie_results'][0]['poster_path'])) {
            $img = $data['movie_results'][0]['poster_path'];

            return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $img;
        }

        if (isset($data['Poster']) && 'N/A' != $data['Poster']) {
            return $data['Poster'];
        }

        return '';
    }
}
