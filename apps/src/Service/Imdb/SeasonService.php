<?php

namespace Labstag\Service\Imdb;

use DateTime;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Message\EpisodeMessage;
use Labstag\Repository\SeasonRepository;
use Labstag\Service\FileService;
use Symfony\Component\Messenger\MessageBusInterface;

final class SeasonService
{
    public function __construct(
        private FileService $fileService,
        private MessageBusInterface $messageBus,
        private SeasonRepository $seasonRepository,
        private EpisodeService $episodeService,
        private TheMovieDbApi $theMovieDbApi,
        private PersonService $personService,
    )
    {
    }

    public function getSeason(Serie $serie, array $data): ?Season
    {
        $season = $this->seasonRepository->findOneBy(
            [
                'refserie' => $serie,
                'number'   => $data['season_number'],
            ]
        );

        if ($season instanceof Season) {
            if (0 == $data['episode_count']) {
                $this->seasonRepository->delete($season);

                return null;
            }

            return $season;
        }

        if (0 == $data['episode_count']) {
            return null;
        }

        $season = new Season();
        $season->setEnable(true);
        $season->setRefserie($serie);
        $season->setNumber($data['season_number']);
        $season->setTitle($data['name']);

        return $season;
    }

    public function getSeasons(Serie $serie): array
    {
        return $this->seasonRepository->findBy(
            ['refserie' => $serie],
            ['number' => 'ASC']
        );
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
        $details = $this->theMovieDbApi->getDetailsSeason($season);
        if (!isset($details['tmdb']) || is_null($details['tmdb'])) {
            $this->seasonRepository->delete($season);

            return false;
        }

        $statuses = [
            $this->updateSeason($season, $details),
            $this->updateImagePoster($season, $details),
            $this->updateImageBackdrop($season),
            $this->updateCredits($season, $details),
            $this->updateEpisodes($season, $details),
        ];

        return in_array(true, $statuses, true);
    }

    private function updateCredits(Season $season, array $details): bool
    {
        foreach ($season->getCastings() as $casting) {
            $season->removeCasting($casting);
        }

        if (isset($details['credits']['cast']) && is_array($details['credits']['cast'])) {
            foreach ($details['credits']['cast'] as $cast) {
                $person = $this->personService->getPerson($cast);
                $casting = $this->personService->addToCastingSeason($person, $season, $cast);
                $season->addCasting($casting);
            }
        }

        if (isset($details['credits']['crew']) && is_array($details['credits']['crew'])) {
            foreach ($details['credits']['crew'] as $crew) {
                $person = $this->personService->getPerson($crew);
                $casting =$this->personService->addToCastingSeason($person, $season, $crew);
                $season->addCasting($casting);
            }
        }

        return true;
    }

    private function updateEpisodes(Season $season, array $details): bool
    {
        if (isset($details['tmdb']['episodes']) && is_array($details['tmdb']['episodes'])) {
            foreach ($details['tmdb']['episodes'] as $episodeData) {
                $episode = $this->episodeService->getEpisode($season, $episodeData);
                $this->episodeService->save($episode);
            }
        }

        $episodes = $this->episodeService->getEpisodes($season);
        foreach ($episodes as $episode) {
            $this->messageBus->dispatch(new EpisodeMessage($episode->getId()));
        }

        return true;
    }

    private function updateImageBackdrop(Season $season): bool
    {
        $images = [];
        foreach ($season->getEpisodes() as $episode) {
            $file     = $episode->getImg();
            if (is_null($file)) {
                continue;
            }

            $images[] = $this->fileService->getFileInAdapter('episode', $file);
        }

        if ([] === $images) {
            return false;
        }

        $patchwork = $this->fileService->setImgPatchwork($images);
        if (!is_null($patchwork)) {
            $this->fileService->setUploadedFile($patchwork, $season, 'backdropFile');
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImagePoster(Season $season, array $details): bool
    {
        $poster = $this->theMovieDbApi->images()->getPosterUrl($details['tmdb']['poster_path'] ?? '');
        if (is_null($poster)) {
            return false;
        }

        $this->fileService->setUploadedFile($poster, $season, 'posterFile');

        return true;
    }

    private function updateSeason(Season $season, array $details): bool
    {
        $season->setTitle($details['tmdb']['name']);
        $airDate = (is_null($details['tmdb']['air_date']) || empty($details['tmdb']['air_date'])) ? null : new DateTime(
            $details['tmdb']['air_date']
        );
        $season->setAirDate($airDate);
        $season->setTmdb($details['tmdb']['id']);
        $season->setOverview($details['tmdb']['overview']);
        $season->setVoteAverage($details['tmdb']['vote_average']);
        if (isset($details['tmdb']['overview']) && '' != $details['tmdb']['overview']) {
            $season->setOverview($details['tmdb']['overview']);
        }

        return true;
    }
}
