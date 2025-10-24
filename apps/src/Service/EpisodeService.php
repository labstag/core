<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\Episode;
use Labstag\Entity\Season;
use Labstag\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EpisodeService
{
    private const STATUSOK = 200;

    public function __construct(
        private string $tmdbapiKey,
        private CacheService $cacheService,
        private HttpClientInterface $httpClient,
        protected EpisodeRepository $episodeRepository,
    )
    {
    }

    public function getEpisode(Season $season, int $number): ?Episode
    {
        $episode = $this->episodeRepository->findOneBy(
            [
                'refseason' => $season,
                'number'    => $number,
            ]
        );

        if ($episode instanceof Episode) {
            return $episode;
        }

        $episode = new Episode();
        $episode->setEnable(true);
        $episode->setRefseason($season);
        $episode->setNumber($number);

        return $episode;
    }

    public function save(Episode $episode): void
    {
        $this->episodeRepository->persist($episode);
    }

    public function update(Episode $episode): bool
    {
        $tmdb = $episode->getRefseason()->getRefserie()->getTmdb();
        $seasonNumber = $episode->getRefseason()->getNumber();
        $episodeNumber = $episode->getNumber();
        $details       = $this->getDetails($tmdb, $seasonNumber, $episodeNumber);
        $this->updateImage($episode, $details);
        $episode->setOverview($details['overview']);
        $episode->setTmdb($details['id']);
        $episode->setTitle($details['name']);
        $episode->setVoteAverage($details['vote_average']);
        $episode->setVoteCount($details['vote_count']);

        $airDate = empty($details['air_date']) ? null : new DateTime($details['air_date']);
        $episode->setAirDate($airDate);

        return true;
    }

    private function getDetails(int $tmdb, int $seasonNumber, int $episodeNumber): ?array
    {
        $cacheKey = 'tmdb-serie_find_' . $tmdb . '_season_' . $seasonNumber . '_episode_' . $episodeNumber;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdb, $seasonNumber, $episodeNumber): ?array {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdb . '/season/' . $seasonNumber . '/episode/' . $episodeNumber . '?language=fr-FR';
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

                $item->expiresAfter(60);

                return $data;
            },
            60
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgEpisode(array $data): string
    {
        if (isset($data['still_path'])) {
            return $this->getImgImdb($data['still_path']);
        }

        return '';
    }

    private function getImgImdb(string $img): string
    {
        return 'https://media.themoviedb.org/t/p/w227_and_h127_bestv2' . $img;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImage(
        Episode $episode,
        array $details,
    ): bool
    {
        $poster = $this->getImgEpisode($details);
        if ('' === $poster) {
            return false;
        }

        if ('' != $episode->getImg()) {
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

            $episode->setImgFile($uploadedFile);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
