<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Exception;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Episode;
use Labstag\Entity\Season;
use Labstag\Repository\EpisodeRepository;
use Labstag\Service\FileService;

final class EpisodeService
{
    public function __construct(
        private FileService $fileService,
        private EpisodeRepository $episodeRepository,
        private TheMovieDbApi $theMovieDbApi,
    )
    {
    }

    public function getEpisode(Season $season, array $data): Episode
    {
        $episode = $this->episodeRepository->findOneBy(
            [
                'refseason' => $season,
                'number'    => $data['episode_number'],
            ]
        );

        if ($episode instanceof Episode) {
            return $episode;
        }

        $episode = new Episode();
        $episode->setEnable(true);
        $episode->setRefseason($season);
        $episode->setTitle($data['name']);
        $episode->setNumber($data['episode_number']);

        return $episode;
    }

    public function getEpisodes(Season $season): array
    {
        return $this->episodeRepository->findBy(
            ['refseason' => $season],
            ['number' => 'ASC']
        );
    }

    public function save(Episode $episode): void
    {
        $this->episodeRepository->save($episode);
    }

    public function update(Episode $episode): bool
    {
        $details = $this->theMovieDbApi->getDetailsEpisode($episode);
        if ([] === $details) {
            $this->episodeRepository->delete($episode);

            return false;
        }

        $statuses = [
            $this->updateEpisode($episode, $details),
            $this->updateImage($episode, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function updateEpisode(Episode $episode, array $details): bool
    {
        $episode->setOverview($details['tmdb']['overview']);
        $episode->setTmdb($details['tmdb']['id']);
        $episode->setTitle($details['tmdb']['name']);

        $voteAverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $episode->setVoteAverage($voteAverage);
        $episode->setVoteCount($details['tmdb']['vote_count']);
        $episode->setRuntime($details['tmdb']['runtime']);

        $airDate = (is_null($details['tmdb']['air_date']) || empty($details['tmdb']['air_date'])) ? null : new DateTime(
            $details['tmdb']['air_date']
        );
        $episode->setAirDate($airDate);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImage(Episode $episode, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getStillUrl($details['tmdb']['still_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        if ('' !== (string) $episode->getImg()) {
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
