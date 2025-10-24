<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\Category;
use Labstag\Entity\Serie;
use Labstag\Message\SeasonMessage;
use Labstag\Repository\CategoryRepository;
use Labstag\Repository\SerieRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SerieService
{
    private const STATUSOK = 200;

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
    private array $genres = [];

    /**
     * @var array<string, mixed>
     */
    private array $year = [];

    public function __construct(
        private CacheService $cacheService,
        private MessageBusInterface $messageBus,
        private SeasonService $seasonService,
        private HttpClientInterface $httpClient,
        private SerieRepository $serieRepository,
        private CategoryRepository $categoryRepository,
        private string $tmdbapiKey,
    )
    {
    }

    public function deleteOldCategory(): void
    {
        $data = $this->categoryRepository->findAllByTypeSerieWithoutSerie();
        foreach ($data as $category) {
            $total = count($category->getMovies());
            if (0 !== $total) {
                continue;
            }

            $this->categoryRepository->delete($category);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCategoryForForm(): array
    {
        if ([] !== $this->category) {
            return $this->category;
        }

        $data       = $this->categoryRepository->findAllByTypeSerieEnable();
        $categories = [];
        foreach ($data as $category) {
            $categories[$category->getTitle()] = $category->getSlug();
        }

        $this->category = $categories;

        return $categories;
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
     * @return array<string, mixed>|null
     */
    public function getDetailsTmdb(string $imdbId): ?array
    {
        if ('' === $this->tmdbapiKey) {
            return null;
        }

        $cacheKey = 'tmdb-serie_find_' . $imdbId;

        return $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($imdbId) {
                $url      = 'https://api.themoviedb.org/3/find/' . $imdbId . '?external_source=imdb_id&language=fr-FR';
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
                if (0 === count($data['tv_results'])) {
                    $item->expiresAfter(0);

                    return null;
                }

                $item->expiresAfter(86400);

                return $data;
            },
            60
        );
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
            $choices[$label] = $label;
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
            $data   = $this->getDetailsTmdb($serie->getImdb());
            if (null !== $data && isset($data['tv_results'][0]['id'])) {
                $tmdbId = $data['tv_results'][0]['id'];
            }
        }

        if (empty($tmdbId)) {
            return [];
        }

        $details = $this->getDetailsTmdbSerie($details, $tmdbId);

        return $this->getTrailersTmdbSerie($details, $tmdbId);
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    private function getDetailsTmdbSerie(
        array $details,
        string $tmdbId,
    ): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $cacheKey = 'tmdb-serie_' . $tmdbId;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdbId . '?language=fr-FR';
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

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );
        if (null == $data) {
            return $details;
        }

        $details['tmdb'] = $data;

        return $details;
    }

    private function getImgImdb(string $img): string
    {
        return 'https://image.tmdb.org/t/p/w300_and_h450_bestv2' . $img;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getImgMovie(array $data): string
    {
        if (isset($data['tmdb']['poster_path'])) {
            return $this->getImgImdb($data['tmdb']['poster_path']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $details
     *
     * @return array<string, mixed>
     */
    private function getTrailersTmdbSerie(
        array $details,
        string $tmdbId,
    ): array
    {
        if ('' === $this->tmdbapiKey) {
            return $details;
        }

        $cacheKey = 'tmdb-serie-trailers_' . $tmdbId;

        $data = $this->cacheService->get(
            $cacheKey,
            function (ItemInterface $item) use ($tmdbId) {
                $url      = 'https://api.themoviedb.org/3/tv/' . $tmdbId . '/videos?language=fr-FR';
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

                $item->expiresAfter(86400);

                return json_decode($response->getContent(), true);
            },
            60
        );

        if (null == $data) {
            return $details;
        }

        $details['trailers'] = $data;

        return $details;
    }

    /**
     * @return array<string, mixed>
     */
    private function initGenres(): array
    {
        if ([] !== $this->genres) {
            return $this->genres;
        }

        $data       = $this->categoryRepository->findAllByTypeSerie();
        $categories = [];
        foreach ($data as $category) {
            $title              = trim((string) $category->getTitle());
            $categories[$title] = $category;
        }

        $this->genres = $categories;

        return $categories;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function setCertification(
        array $details,
        Serie $serie,
    ): void
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

    /**
     * @param array<string, mixed> $details
     */
    private function updateCategory(
        Serie $serie,
        array $details,
    ): bool
    {
        if (!isset($details['tmdb']['genres']) || 0 === count($details['tmdb']['genres'])) {
            return false;
        }

        $this->initGenres();

        foreach ($serie->getCategories() as $category) {
            $serie->removeCategory($category);
        }

        foreach ($details['tmdb']['genres'] as $genre) {
            $title = trim((string) $genre['name']);
            if (isset($this->genres[$title])) {
                $category = $this->genres[$title];
                $serie->addCategory($category);
                continue;
            }

            $category = new Category();
            $category->setTitle($title);
            $category->setType('serie');
            $this->categoryRepository->save($category);
            $this->genres[$title] = $category;

            $serie->addCategory($category);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateImageMovie(
        Serie $serie,
        array $details,
    ): bool
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

            $serie->setImgFile($uploadedFile);

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
    private function updateSerie(
        Serie $serie,
        array $details,
    ): bool
    {
        if (!isset($details['tmdb'])) {
            return false;
        }

        $adult = isset($details['tmdb']['adult']) && (bool) $details['tmdb']['adult'];
        $serie->setAdult($adult);
        $serie->setTitle((string) $details['tmdb']['name']);

        $this->setCertification($details, $serie);

        $tagline = (string) $details['tmdb']['tagline'];
        if ('' !== $tagline && '0' !== $tagline) {
            $serie->setCitation($tagline);
        }

        $overview = (string) $details['tmdb']['overview'];
        if ('' !== $overview && '0' !== $overview) {
            $serie->setDescription($overview);
        }

        $voteEverage = (float) ($details['tmdb']['vote_average'] ?? 0);
        $voteCount   = (int) ($details['tmdb']['vote_count'] ?? 0);

        $serie->setEvaluation($voteEverage);
        $serie->setVotes($voteCount);

        $serie->setCountries($details['tmdb']['origin_country']);

        $serie->setTmdb($details['tmdb']['id']);

        $serie->setReleaseDate(new DateTime($details['tmdb']['first_air_date']));
        $serie->setLastreleaseDate(new DateTime($details['tmdb']['last_air_date']));

        $this->updateImageMovie($serie, $details);

        return true;
    }

    /**
     * @param array<string, mixed> $details
     */
    private function updateTrailer(
        Serie $serie,
        array $details,
    ): bool
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
