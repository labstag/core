<?php

namespace Labstag\Service;

use Exception;
use Labstag\Entity\Movie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieService
{
    private const STATUSOK = 200;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string $omdbapiKey,
        protected string $tmdbapiKey,
    )
    {
    }

    public function getDetails(string $imdbId): array
    {
        $details = [];
        $omdb    = $this->getDetailsOmDBAPI($imdbId);
        if (null !== $omdb && isset($omdb['Poster'])) {
            return $omdb;
        }

        $tmdb = $this->getDetailsTmdb($imdbId);
        if (null !== $tmdb && isset($tmdb['movie_results'][0]['poster_path'])) {
            return $tmdb;
        }

        return $details;
    }

    public function update(Movie $movie): bool
    {
        $details = $this->getDetails($movie->getImdb());
        $poster  = $this->getImg($details);
        if ('' === $poster || 'N/A' === $poster) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename($poster),
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

        $url      = 'http://www.omdbapi.com/?i=tt'.$imdbId.'&apikey='.$this->omdbapiKey;
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

        $url      = 'https://api.themoviedb.org/3/find/tt'.$imdbId.'?external_source=imdb_id&language=fr';
        $response = $this->httpClient->request(
            'GET',
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->tmdbapiKey,
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
        if (isset($data['Poster'])) {
            return $data['Poster'];
        }

        if (isset($data['movie_results'][0]['poster_path'])) {
            $img = $data['movie_results'][0]['poster_path'];

            return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2'.$img;
        }

        return '';
    }
}
