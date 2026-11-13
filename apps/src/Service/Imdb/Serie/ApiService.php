<?php

declare(strict_types=1);

namespace Labstag\Service\Imdb\Serie;

use DateTime;
use Labstag\Api\TheMovieDbApi;
use Labstag\Entity\Serie;
use Labstag\Repository\SerieRepository;
use Labstag\Service\ConfigurationService;

final class ApiService
{
    public function __construct(
        private TheMovieDbApi $theMovieDbApi,
        private ConfigurationService $configurationService,
        private SerieRepository $serieRepository,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetailsSerie(Serie $serie): array
    {
        return $this->theMovieDbApi->getDetailsSerie($serie);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSerieApi(array $data, int $page = 1): array
    {
        $series = [];
        $tmdbs  = $this->serieRepository->getAllTmdb();
        $search = '';

        if (isset($data['imdb']) && !empty($data['imdb'])) {
            $results = $this->theMovieDbApi->other()->findByImdb($data['imdb']);
            if (isset($results['tv_results'])) {
                $series = $results['tv_results'];
            }

            return $this->updateResult($series, $tmdbs);
        }

        if (isset($data['title'])) {
            $search = $data['title'];
        }

        $locale  = $this->configurationService->getLocaleTmdb();
        $results = $this->theMovieDbApi->tvserie()->search(searchQuery: $search, page: $page, language: $locale);
        if (isset($results['results'])) {
            $series = $results['results'];
        }

        return $this->updateResult($series, $tmdbs);
    }

    /**
     * @param mixed[] $series
     * @param mixed[] $tmdbs
     *
     * @return array<string, mixed>
     */
    private function updateResult(array $series, array $tmdbs): array
    {
        foreach ($series as &$serie) {
            $serie['first_air_date'] = empty($serie['first_air_date']) ? null : new DateTime($serie['first_air_date']);
            $serie['poster_path']    = $this->theMovieDbApi->images()->getPosterUrl($serie['poster_path'] ?? '', 100);
        }

        return array_filter($series, fn (array $serie): bool => !in_array($serie['id'], $tmdbs));
    }
}
