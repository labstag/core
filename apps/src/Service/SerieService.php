<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Api\TmdbApi;
use Labstag\Entity\Serie;
use Labstag\Entity\SerieCategory;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\SerieRepository;
use Symfony\Component\Messenger\MessageBusInterface;

final class SerieService
{

    /**
     * @var array<string, mixed>
     */
    private array $category = [];

    /**
     * @var array<string, mixed>
     */
    private array $country = [];

    /**
     * @var array<string, mixed>
     */
    private array $year = [];

    public function __construct(
        private MessageBusInterface $messageBus,
        private FileService $fileService,
        private SeasonService $seasonService,
        private SerieRepository $serieRepository,
        private CategoryRepository $categoryRepository,
        private CategoryService $categoryService,
        private TmdbApi $tmdbApi,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCountryForForm(): array
    {
        if ([] !== $this->country) {
            return $this->country;
        }

        $country    = $this->serieRepository->findAllUniqueCountries();

        $this->country = $country;

        return $country;
    }

    /**
     * @return mixed[]
     */
    public function getSeriesChoice(): array
    {
        $series = $this->serieRepository->findBy(
            [],
            ['title' => 'ASC']
        );
        $choices = [];
        /** @var Serie $serie */
        foreach ($series as $serie) {
            $label           = $serie->getTitle();
            $id              = $serie->getId();
            $choices[$label] = $id;
        }

        return $choices;
    }

    /**
     * @return array<string, mixed>
     */
    public function getYearForForm(): array
    {
        if ([] !== $this->year) {
            return $this->year;
        }

        $data = $this->serieRepository->findAllUniqueYear();
        $year = [];
        foreach ($data as $value) {
            $year[$value] = $value;
        }

        $this->year = $year;

        return $year;
    }

    public function update(Serie $serie): bool
    {
        if (in_array($serie->getImdb(), [null, '', '0'], true)) {
            return false;
        }

        $details = $this->getDetails($serie);

        $statuses = [
            $this->updateSerie($serie, $details),
            $this->updateCategory($serie, $details),
            $this->updateTrailer($serie, $details),
            $this->updateSeasons($serie, $details),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function getDetails(Serie $serie): array
    {
        $details = [];

        $tmdbId = $serie->getTmdb();
        if (null === $tmdbId || '' === $tmdbId || '0' === $tmdbId) {
            $data   = $this->tmdbApi->findByImdb($serie->getImdb());
            if (null !== $data && isset($data['tv_results'][0]['id'])) {
                $tmdbId = $data['tv_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return [];
        }

        $details = $this->tmdbApi->getDetailsSerie($details, $tmdbId);

        return $this->tmdbApi->getTrailersSerie($details, $tmdbId);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgMovie(array $data): string
    {
        if (isset($data['tmdb']['poster_path'])) {
            return $this->tmdbApi->getImgw300h450($data['tmdb']['poster_path']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $details
     */
    private function setCertification(array $details, Serie $serie): void
    {
        if (!isset($details['release_dates']['results']) || 0 === count($details['release_dates']['results'])) {
            return;
        }

        foreach ($details['release_dates']['results'] as $result) {
            if ('FR' != $result['iso_3166_1']) {
                continue;
            }

            foreach ($result['release_dates'] as $release) {
                if ('' === (string) $release['certification']) {
                    continue;
                }

                $serie->setCertification((string) $release['certification']);

                return;
            }
        }
    }

    private function setCitation(Serie $serie, array $details): void
    {
        $tagline = (string) $details['tmdb']['tagline'];
        if ('' !== $tagline && '0' !== $tagline) {
            $serie->setCitation($tagline);
        }
    }

    private function setDescription(Serie $serie, array $details): void
    {
        $overview = (string) $details['tmdb']['overview'];
        if ('' !== $overview && '0' !== $overview) {
            $serie->setDescription($overview);
        }
    }

    private function setLastreleaseDate(Serie $serie, array $details): void
    {
        $lastReleaseDate = (is_null(
            $details['tmdb']['last_air_date']
        ) || empty($details['tmdb']['last_air_date'])) ? null : new DateTime($details['tmdb']['last_air_date']);
        $serie->setLastreleaseDate($lastReleaseDate);
    }

    private function setReleaseDate(Serie $serie, array $details): void
    {
        $releaseDate = (is_null(
            $details['tmdb']['first_air_date']
        ) || empty($details['tmdb']['first_air_date'])) ? null : new DateTime($details['tmdb']['first_air_date']);
        $serie->setReleaseDate($releaseDate);
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateCategory(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        foreach ($serie->getCategories() as $category) {
            $serie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title    = trim((string) $genre['name']);
            $category = $this->categoryService->getType($title, SerieCategory::class);

            $serie->addCategory($category);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageMovie(Serie $serie, array $details): bool
    {
        $poster = $this->getImgMovie($details);
        if ('' === $poster) {
            return false;
        }

        if ('' != $serie->getImg()) {
            return false;
        }

        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($poster));
            $this->fileService->setUploadedFile($tempPath, $serie, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateSeasons(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb']['number_of_seasons'])) {
            return false;
        }

        for ($number = 1; $number <= (int) $details['tmdb']['number_of_seasons']; ++$number) {
            $season = $this->seasonService->getSeason($serie, $number);
            $this->seasonService->save($season);
            $this->messageBus->dispatch(new SeasonMessage($season->getId()));
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateSerie(Serie $serie, array $details): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $serie->setInProduction((bool) $details['tmdb']['in_production']);
        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $serie->setAdult($adult);
        $serie->setTitle((string) $details['tmdb']['name']);

        $this->setCertification($details, $serie);
        $this->setCitation($serie, $details);
        $this->setDescription($serie, $details);

        $voteEverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $serie->setEvaluation($voteEverage);
        $serie->setVotes($voteCount);

        $serie->setCountries($details['tmdb']['origin_country']);

        $serie->setTmdb($details['tmdb']['id']);
        $this->setReleaseDate($serie, $details);
        $this->setLastreleaseDate($serie, $details);
        $this->updateImageMovie($serie, $details);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateTrailer(Serie $serie, array $details): bool
    {
        if (!isset($details['trailers'])) {
            return false;
        }

        $find = false;

        foreach ($details['trailers']['results'] as $result) {
            if ('YouTube' == $result['site'] && 'Trailer' == $result['type']) {
                $url = 'https://www.youtube.com/watch?v=' . $result['key'];
                $serie->setTrailer($url);

                $find = true;

                break;
            }
        }

        return $find;
    }
}
