<?php

namespace Labstag\Service\Igdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Api\IgdbApi;
use Labstag\Entity\Game;
use Labstag\Entity\GameCategory;
use Labstag\Entity\Platform;
use Labstag\Message\SearchGameMessage;
use Labstag\Service\CategoryService;
use Labstag\Service\FileService;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Messenger\MessageBusInterface;

final class GameService extends AbstractIgdb
{
    public function __construct(
        IgdbApi $igdbApi,
        EntityManagerInterface $entityManager,
        FileService $fileService,
        private MessageBusInterface $messageBus,
        private FranchiseService $franchiseService,
        private CategoryService $categoryService,
    )
    {
        parent::__construct($igdbApi, $entityManager, $fileService);
    }

    public function addByApi(string $id, $platformId): ?Game
    {
        $result = $this->getApiGameId($id);
        if (is_null($result)) {
            return null;
        }

        $game = $this->getGame($result);
        $this->update($game);

        $platform = $this->entityManager->getRepository(Platform::class)->find($platformId);
        if ($platform instanceof Platform) {
            $game->addPlatform($platform);
        }

        $this->entityManager->getRepository(Game::class)->save($game);

        return $game;
    }

    public function getGameApi(array $data, int $limit, int $offset): array
    {
        $entityRepository     = $this->entityManager->getRepository(Game::class);
        $platformRepository   = $this->entityManager->getRepository(Platform::class);
        $igbds                = $entityRepository->getAllIgdb();
        $games                = [];
        $where                = [];
        $search               = $data['title'] ?? '';
        if (isset($data['platform']) && !empty($data['platform'])) {
            $platform = $platformRepository->find($data['platform']);
            if ($platform instanceof Platform) {
                $where[] = 'platforms = (' . $platform->getIgdb() . ')';
            }
        }

        if (isset($data['franchise']) && !empty($data['franchise'])) {
            $where[] = 'franchises.name ~ "' . $data['franchise'] . '"';
        }

        if (isset($data['type']) && !empty($data['type'])) {
            $where[] = 'game_type = ' . $data['type'];
        }

        if (isset($data['number']) && !empty($data['number'])) {
            $where[] = 'id = ' . $data['number'];
        }

        $body  = $this->igdbApi->setBody(
            search: $search,
            fields: [
                '*',
                'cover.*',
                'game_type.*',
            ],
            where: $where,
            limit: $limit,
            offset: $offset
        );
        $games = $this->igdbApi->setUrl('games', $body);
        if (is_null($games)) {
            $games = [];
        }

        $games = array_filter($games, fn (array $game): bool => !in_array($game['id'], $igbds));

        return array_filter($games, fn (array $game): bool => isset($game['first_release_date']));
    }

    public function getimportCsvFile(string $path): array
    {
        $csv = new Csv();
        $csv->setDelimiter(',');
        $csv->setSheetIndex(0);

        $spreadsheet = $csv->load($path);
        $worksheet   = $spreadsheet->getActiveSheet();

        return $this->generateJsonCSV($worksheet);
    }

    public function getResultApiForData(array $data): ?array
    {
        $name = trim((string) $data['Nom']);
        $body    = $this->igdbApi->setBody(search: $name, fields: ['*', 'cover.*', 'game_type.*', 'alternative_name.*']);
        $results = $this->igdbApi->setUrl('games', $body);
        if (is_null($results)) {
            return null;
        }

        if (0 === count($results)) {
            return null;
        }

        if (1 === count($results)) {
            return $results[0];
        }

        foreach ($results as $result) {
            $alternativeNames = isset($result['alternative_names']) && is_array($result['alternative_names']) ? $result['alternative_names'] : [];
            foreach ($alternativeNames ?? [] as $alternativeName) {
                if ($alternativeName['name'] == $name || strtolower((string) $alternativeName['name']) === strtolower($name)) {
                    return $result;
                }
            }

            if ($result['name'] == $name || strtolower((string) $result['name']) === strtolower($name)) {
                return $result;
            }
        }
        return null;
    }

    public function importFile($file, string $platform): bool
    {
        $file = $this->fileService->getFileInAdapter('private', $file);
        if (is_null($file)) {
            return false;
        }

        $mimeType = mime_content_type($file);
        if ('text/csv' == $mimeType) {
            return $this->importCsvFile($file, $platform);
        }

        unlink($file);

        return true;
    }

    public function update(Game $game): bool
    {
        $result = $this->getApiGameId($game->getIgdb() ?? '0');
        if (0 == count($result)) {
            return false;
        }

        $statuses = [
            $this->updateImage($game, $result),
            $this->updateFranchises($game, $result),
            $this->updateScreenshots($game, $result),
            $this->updateArtworks($game, $result),
            $this->updateVideos($game, $result),
            $this->updateGenres($game, $result),
        ];

        return in_array(true, $statuses, true);
    }

    /**
     * @return list<array>
     */
    private function generateJsonCSV(Worksheet $worksheet): array
    {
        $dataJson    = [];
        $headers     = [];
        foreach ($worksheet->getRowIterator() as $i => $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            if (1 === $i) {
                foreach ($cellIterator as $cell) {
                    $headers[] = trim((string) $cell->getValue());
                }

                continue;
            }

            $columns = [];
            foreach ($cellIterator as $cell) {
                $columns[] = trim((string) $cell->getValue());
            }

            $dataJson[] = array_combine($headers, $columns);
        }

        return $dataJson;
    }

    private function getApiGameArtworksIds(array $artworkIds): ?array
    {
        $where = ['id = (' . implode(',', $artworkIds) . ')'];
        $body  = $this->igdbApi->setBody(where: $where, limit: count($artworkIds));

        $results = $this->igdbApi->setUrl('artworks', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGameCoverId(array $data): ?array
    {
        if (!isset($data['cover'])) {
            return null;
        }

        $where = ['id = ' . $data['cover']];
        $body  = $this->igdbApi->setBody(where: $where, limit: 1);

        $results = $this->igdbApi->setUrl('covers', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGameId(string $id): ?array
    {
        $where = ['id = ' . $id];
        $body  = $this->igdbApi->setBody(where: $where, limit: 1);

        $results = $this->igdbApi->setUrl('games', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    private function getApiGameScreenshotsIds(array $screenshotIds): ?array
    {
        $where = ['id = (' . implode(',', $screenshotIds) . ')'];
        $body  = $this->igdbApi->setBody(where: $where, limit: count($screenshotIds));

        $results = $this->igdbApi->setUrl('screenshots', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGameVideosIds(array $artworkIds): ?array
    {
        $where = ['id = (' . implode(',', $artworkIds) . ')'];
        $body  = $this->igdbApi->setBody(where: $where, limit: count($artworkIds));

        $results = $this->igdbApi->setUrl('game_videos', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGenreId(int $id): ?array
    {
        $where = ['id = ' . $id];
        $body  = $this->igdbApi->setBody(where: $where, limit: 1);

        $results = $this->igdbApi->setUrl('genres', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getGame(array $data): Game
    {
        $entityRepository = $this->entityManager->getRepository(Game::class);
        $game             = $entityRepository->findOneBy(
            [
                'igdb' => $data['id'],
            ]
        );
        if ($game instanceof Game) {
            return $game;
        }

        $game = new Game();
        $game->setIgdb($data['id']);
        $game->setTitle($data['name']);
        $game->setUrl($data['url']);
        if (isset($data['first_release_date'])) {
            $datetime = new DateTime();
            $game->setReleaseDate($datetime->setTimestamp($data['first_release_date']));
        }

        return $game;
    }

    private function importCsvFile(string $path, string $platform): bool
    {
        $data = $this->getimportCsvFile($path);
        foreach ($data as $row) {
            $this->messageBus->dispatch(new SearchGameMessage($row, $platform));
        }

        return true;
    }

    private function updateArtworks(Game $game, array $data): bool
    {
        if (!isset($data['artworks']) || !is_array($data['artworks'])) {
            $game->setArtworks([]);

            return true;
        }

        $results  = $this->getApiGameArtworksIds($data['artworks']);
        $artworks = [];
        foreach ($results as $result) {
            if (is_null($result)) {
                continue;
            }

            $artworks[] = $this->igdbApi->buildImageUrl($result['image_id'], 'original');
        }

        $game->setArtworks($artworks);

        return true;
    }

    private function updateFranchises(Game $game, array $data): bool
    {
        if (!isset($data['franchises']) || !is_array($data['franchises'])) {
            foreach ($game->getFranchises() as $franchise) {
                $game->removeFranchise($franchise);
            }

            return true;
        }

        foreach ($data['franchises'] as $franchiseId) {
            $franchise = $this->franchiseService->addByApi((string) $franchiseId);
            if (is_null($franchise)) {
                continue;
            }

            $game->addFranchise($franchise);
        }

        return true;
    }

    private function updateGenres(Game $game, array $data): bool
    {
        if (!isset($data['genres']) || !is_array($data['genres'])) {
            foreach ($game->getCategories() as $genre) {
                $game->removeCategory($genre);
            }

            return true;
        }

        foreach ($data['genres'] as $genreId) {
            $result   = $this->getApiGenreId($genreId);
            $category = $this->categoryService->getType($result[0]['name'], GameCategory::class);

            $game->addCategory($category);
        }

        return true;
    }

    private function updateImage(Game $game, array $data): bool
    {
        $result = $this->getApiGameCoverId($data);
        if (is_null($result)) {
            return false;
        }

        $imageUrl = $this->igdbApi->buildImageUrl($result[0]['image_id'], 'original');
        try {
            $tempPath = tempnam(sys_get_temp_dir(), 'poster_');

            // Télécharger l'image et l'écrire dans le fichier temporaire
            file_put_contents($tempPath, file_get_contents($imageUrl));
            $this->fileService->setUploadedFile($tempPath, $game, 'imgFile');

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function updateScreenshots(Game $game, array $data): bool
    {
        if (!isset($data['screenshots']) || !is_array($data['screenshots'])) {
            $game->setScreenshots([]);

            return true;
        }

        $results     = $this->getApiGameScreenshotsIds($data['screenshots']);
        $screenshots = [];
        foreach ($results as $result) {
            if (is_null($result)) {
                continue;
            }

            $screenshots[] = $this->igdbApi->buildImageUrl($result['image_id'], 'original');
        }

        $game->setScreenshots($screenshots);

        return true;
    }

    private function updateVideos(Game $game, array $data): bool
    {
        if (!isset($data['videos']) || !is_array($data['videos'])) {
            $game->setVideos([]);

            return true;
        }

        $results = $this->getApiGameVideosIds($data['videos']);
        $videos  = [];
        foreach ($results as $result) {
            if (is_null($result)) {
                continue;
            }

            $videos[] = $result['video_id'];
        }

        $game->setVideos($videos);

        return true;
    }
}
