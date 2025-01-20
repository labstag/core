<?php

namespace Labstag\Service;

use Exception;
use Labstag\Entity\Movie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImdbService
{
    private const STATUSOK = 200;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string $apiKey
    )
    {

    }

    public function getDetails(string $imdbId): array
    {
        $response = $this->httpClient->request('GET', $url);
        if ($this->apiKey == '') {
            return $response->toArray();
        }

        $url = 'http://www.omdbapi.com/?i=tt'.$imdbId.'&apikey='.$this->apiKey;
        if ($response->getStatusCode() === self::STATUSOK) {
            return $response->toArray();
        }

        throw new Exception('Erreur lors de la requête API');
    }

    public function update(Movie $movie): bool
    {
        $details = $this->getDetails($movie->getImdb());
        if (!isset($details['Poster']) || $details['Poster'] == '' || $details['Poster'] == 'N/A') {
            return false;
        }

        $poster = $details['Poster'];
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
        } catch (Exception $e) {
            return false;
        }
    }
}
