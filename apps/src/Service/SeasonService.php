<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Api\TmdbApi;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Message\EpisodeMessage;
use Labstag\Repository\SeasonRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class SeasonService
{
    public function __construct(
        private LoggerInterface $logger,
        private FileService $fileService,
        private MessageBusInterface $messageBus,
        private SeasonRepository $seasonRepository,
        private EpisodeService $episodeService,
        private TmdbApi $tmdbApi,
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
        $this->seasonRepository->save($season);
    }

    public function update(Season $season): bool
    {
        $tmdb = $season->getRefSerie()->getTmdb();
        $numberSeason = $season->getNumber();
        $details      = $this->tmdbApi->getDetailsSerieBySeason($tmdb, $numberSeason);
        if (null === $details) {
            $this->logger->error(
                'Season not found TMDB',
                [
                    'tmdb'          => $tmdb,
                    'season_number' => $numberSeason,
                ]
            );

            $this->seasonRepository->remove($season);
            $this->seasonRepository->flush();

            return false;
        }

        $season->setTitle($details['name']);
        $airDate = (is_null($details['air_date']) || empty($details['air_date'])) ? null : new DateTime(
            $details['air_date']
        );
        $season->setAirDate($airDate);
        $season->setTmdb($details['id']);
        $season->setOverview($details['overview']);
        $season->setVoteAverage($details['vote_average']);
        if (isset($details['overview']) && '' != $details['overview']) {
            $season->setOverview($details['overview']);
        }

        $this->updateImage($season, $details);
        $episodes = count($details['episodes']);
        if (0 === $episodes && 0 === count($season->getEpisodes())) {
            $this->seasonRepository->remove($season);

            return true;
        }

        for ($number = 1; $number <= $episodes; ++$number) {
            $episode = $this->episodeService->getEpisode($season, $number);
            $this->episodeService->save($episode);
            $this->messageBus->dispatch(new EpisodeMessage($episode->getId()));
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgSeason(array $data): string
    {
        if (isset($data['poster_path'])) {
            return $this->tmdbApi->getImgw300h450($data['poster_path']);
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

            $this->fileService->setUploadedFile($tempPath, $season, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
