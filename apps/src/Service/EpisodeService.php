<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Episode;
use Labstag\Entity\Season;
use Labstag\Repository\EpisodeRepository;
use Psr\Log\LoggerInterface;

class EpisodeService
{
    public function __construct(
        private LoggerInterface $logger,
        private FileService $fileService,
        private EpisodeRepository $episodeRepository,
        private TheMovieDbApi $theMovieDbApi,
        private ConfigurationService $configurationService,
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
        $this->episodeRepository->save($episode);
    }

    public function update(Episode $episode): bool
    {
        $tmdb = $episode->getRefseason()->getRefserie()->getTmdb();
        $seasonNumber = $episode->getRefseason()->getNumber();
        $episodeNumber = $episode->getNumber();
        $locale        = $this->configurationService->getLocaleTmdb();
        $details       = $this->theMovieDbApi->tvserie()->getEpisodeDetails($tmdb, $seasonNumber, $episodeNumber, $locale);
        if (!is_array($details)) {
            $this->logger->error(
                'Episode not found TMDB',
                [
                    'tmdb'           => $tmdb,
                    'season_number'  => $seasonNumber,
                    'episode_number' => $episodeNumber,
                ]
            );

            return false;
        }

        $this->updateImage($episode, $details);
        $episode->setOverview($details['overview']);
        $episode->setTmdb($details['id']);
        $episode->setTitle($details['name']);
        $episode->setVoteAverage($details['vote_average']);
        $episode->setVoteCount($details['vote_count']);
        $episode->setRuntime($details['runtime']);

        $airDate = (is_null($details['air_date']) || empty($details['air_date'])) ? null : new DateTime(
            $details['air_date']
        );
        $episode->setAirDate($airDate);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImage(Episode $episode, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getStillUrl($details['still_path'] ?? '');
        if ('' === $poster) {
            return false;
        }

        if ('' != $episode->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $episode, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
