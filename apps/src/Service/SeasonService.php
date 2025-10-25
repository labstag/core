<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\Meta;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Message\EpisodeMessage;
use Labstag\Repository\SeasonRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SeasonService
{
    private const STATUSOK = 200;

    public function __construct(
        private string $tmdbapiKey,
        private CacheService $cacheService,
        private MessageBusInterface $messageBus,
        private HttpClientInterface $httpClient,
        private SeasonRepository $seasonRepository,
        private EpisodeService $episodeService,
    )
    {
    }

    public function getSeason(Serie $serie, int $number): Season
    {
        $season = $this->seasonRepository->findOneBy(
            [
                'refserie' => $serie,
                'number'   => $number,
            ]
        );

        if ($season instanceof Season) {
            return $season;
        }

        $season = new Season();
        $meta   = new Meta();
        $season->setMeta($meta);
        $season->setEnable(true);
        $season->setRefserie($serie);
        $season->setNumber($number);

        return $season;
    }

    /**
     * @return mixed[]
     */
    public function getSeasonsChoice(): array
    {
        $seasons = $this->seasonRepository->findBy(
            [],
            ['number' => 'ASC']
        );
        $choices = [];
        /** @var Season $season */
        foreach ($seasons as $season) {
            $label           = $season->getNumber();
            $choices[$label] = $label;
        }

        return $choices;
    }

    public function save(Season $season): void
    {
        $this->seasonRepository->persist($season);
    }

    public function update(Season $season): bool
    {
        $tmdb = $season->getRefSerie()->getTmdb();
        $numberSeason = $season->getNumber();
        $details      = $this->getDetails($tmdb, $numberSeason);
        if (null === $details) {
            return false;
        }

        $season->setTitle($details['name']);
        $season->setAirDate(new DateTime($details['air_date']));
        $season->setTmdb($details['id']);
        $season->setOverview($details['overview']);
        $season->setVoteAverage($details['vote_average']);
        if (isset($details['overview']) && '' != $details['overview']) {
            $season->setOverview($details['overview']);
        }

        $this->updateImage($season, $details);
        $episodes = count($details['episodes']);
        for ($number = 1; $number <= $episodes; ++$number) {
            $episode = $this->episodeService->getEpisode($season, $number);
            $this->episodeService->save($episode);
            $this->messageBus->dispatch(new EpisodeMessage($episode->getId()));
        }

        return true;
    }

    private function getDetails(int $tmdb, int $number): ?array
    {
        $cacheKey = 'tmdb-serie_find_' . $tmdb . '_season_' . $number;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdb, $number) {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdb . '/season/' . $number . '?language=fr-FR';
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
                if (0 === count($data['episodes'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return $data;
            },
            60
        );
    }

    private function getImgImdb(string $img): string
    {
        return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $img;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgSeason(array $data): string
    {
        if (isset($data['poster_path'])) {
            return $this->getImgImdb($data['poster_path']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImage(Season $season, array $details): bool
    {
        $poster = $this->getImgSeason($details);
        if ('' === $poster) {
            return false;
        }

        if ('' != $season->getImg()) {
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

            $season->setImgFile($uploadedFile);

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
