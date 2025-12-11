<?php

namespace Labstag\Service\Igdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\IgdbApi;
use Labstag\Api\LibreTranslationApi;
use Labstag\Entity\Franchise;
use Labstag\Entity\Game;
use Labstag\Entity\GameCategory;
use Labstag\Entity\Platform;
use Labstag\Service\CategoryService;
use Labstag\Service\FileService;

final class GameService extends AbstractIgdb
{
    public function __construct(
        IgdbApi $igdbApi,
        EntityManagerInterface $entityManager,
        FileService $fileService,
        private LibreTranslationApi $libreTranslationApi,
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

        $game = $this->getGameByRow($result);
        $this->update($game);
        $this->entityManager->getRepository(Game::class)->save($game);

        $platform = $this->entityManager->getRepository(Platform::class)->find($platformId);
        if ($platform instanceof Platform) {
            $game->addPlatform($platform);
        }

        $this->entityManager->getRepository(Game::class)->save($game);

        return $game;
    }

    public function getApiGameId(string $id): ?array
    {
        $where  = ['id = ' . $id];
        $fields = [
            '*',
            'cover.*',
            'genres.*',
            'franchises.*',
            'screenshots.*',
            'artworks.*',
            'videos.*',
        ];
        $body   = $this->igdbApi->setBody(fields: $fields, where: $where, limit: 1);

        $results = $this->igdbApi->setUrl('games', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    public function getFranchise(array $data): Franchise
    {
        $entityRepository = $this->entityManager->getRepository(Franchise::class);
        $franchise        = $entityRepository->findOneBy(
            [
                'igdb' => $data['id'],
            ]
        );
        if ($franchise instanceof Franchise) {
            return $franchise;
        }

        $franchise = new Franchise();
        $franchise->setTitle($data['name']);
        $franchise->setIgdb($data['id']);

        $entityRepository->save($franchise);

        return $franchise;
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

        if (isset($data['type']) && '' != $data['type']) {
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

    public function getResultApiForData(string $name): ?array
    {
        $body    = $this->igdbApi->setBody(search: $name, fields: ['*', 'game_type.*', 'alternative_names.*']);
        $results = $this->igdbApi->setUrl('games', $body);

        if (is_null($results) || 0 === count($results)) {
            return null;
        }

        if (1 === count($results)) {
            return $results[0];
        }

        $nameLower = strtolower($name);

        foreach ($results as $result) {
            if (isset($result['game_type']['id']) && 0 === $result['game_type']['id'] && $this->matchesGameName(
                $result,
                $name,
                $nameLower
            )
            ) {
                return $result;
            }
        }

        foreach ($results as $result) {
            if ($this->matchesGameName($result, $name, $nameLower)) {
                return $result;
            }
        }

        return null;
    }

    public function getResultApiForDataArray(array $data): ?array
    {
        $name   = $data['Nom'] ?? null;
        $name   = $data['name'] ?? $name;

        $fields = [
            '*',
            'game_type.*',
            'alternative_names.*',
        ];
        $where  = [];
        if (isset($data['releasedate'])) {
            $date      = DateTime::createFromFormat('Ymd\THis', $data['releasedate']);
            $timestamp = $date->getTimestamp();
            if (false !== $timestamp) {
                $where[] = 'release_dates.date >= ' . ($timestamp - 604800);
                // -7 days
                $where[] = 'release_dates.date <= ' . ($timestamp + 604800);
                // +7 days
            }
        }

        $body    = $this->igdbApi->setBody(search: $name, fields: $fields, where: $where);
        $results = $this->igdbApi->setUrl('games', $body);

        if (is_null($results) || 0 === count($results)) {
            return null;
        }

        if (1 === count($results)) {
            return $results[0];
        }

        $nameLower = strtolower((string) $name);

        foreach ($results as $result) {
            if (isset($result['game_type']['id']) && 0 === $result['game_type']['id'] && $this->matchesGameName(
                $result,
                $name,
                $nameLower
            )
            ) {
                return $result;
            }
        }

        foreach ($results as $result) {
            if ($this->matchesGameName($result, $name, $nameLower)) {
                return $result;
            }
        }

        return null;
    }

    public function setAnotherName($name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', (string) $name);
        $name = preg_replace('/\s+/', ' ', (string) $name);

        return trim((string) $name);
    }

    public function update(Game $game): bool
    {
        $result = $this->getApiGameId($game->getIgdb() ?? '0');
        if (0 == count($result)) {
            return false;
        }

        $statuses = [
            $this->updateGame($game, $result),
            $this->updateImage($game, $result),
            $this->updateFranchises($game, $result),
            $this->updateScreenshots($game, $result),
            $this->updateArtworks($game, $result),
            $this->updateVideos($game, $result),
            $this->updateGenres($game, $result),
        ];

        return in_array(true, $statuses, true);
    }

    public function updateGame(Game $game, array $data): bool
    {
        $summary = $data['summary'] ?? '';
        $summary = explode("\n", $summary);

        $new     = [];
        foreach ($summary as $text) {
            if ('' !== trim($text)) {
                $translation = $this->libreTranslationApi->translate($text, 'en', 'fr');
                $new[]       = trim($translation['translatedText']);
            }
        }

        $summary = '<p>' . implode('</p><p>', $new) . '</p>';
        $game->setSummary($summary);

        return true;
    }

    private function getGameByRow(array $data): Game
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

        $entityRepository->save($game);

        return $game;
    }

    private function matchesGameName(array $result, string $name, string $nameLower): bool
    {
        $cleanName      = $this->setAnotherName($name);
        $cleanNameLower = strtolower($cleanName);

        // Helper pour vérifier un nom
        $checkName = function (string $gameName) use ($name, $nameLower, $cleanName, $cleanNameLower): bool {
            if ($gameName === $name || strtolower($gameName) === $nameLower) {
                return true;
            }

            $cleanGameName = $this->setAnotherName($gameName);

            return $cleanGameName === $cleanName || strtolower($cleanGameName) === $cleanNameLower;
        };

        // Vérifier le nom principal
        if ($checkName($result['name'])) {
            return true;
        }

        // Vérifier les noms alternatifs
        $alternativeNames = $result['alternative_names'] ?? [];
        if (!is_array($alternativeNames)) {
            return false;
        }

        return array_any(
            $alternativeNames,
            fn ($alternativeName): bool => isset($alternativeName['name']) && $checkName($alternativeName['name'])
        );
    }

    private function updateArtworks(Game $game, array $data): bool
    {
        if (!isset($data['artworks']) || !is_array($data['artworks'])) {
            $game->setArtworks([]);

            return true;
        }

        $artworks = [];
        foreach ($data['artworks'] as $result) {
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

        foreach ($data['franchises'] as $franchiseData) {
            $franchise = $this->getFranchise($franchiseData);
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

        foreach ($data['genres'] as $result) {
            $category = $this->categoryService->getType($result['name'], GameCategory::class);

            $game->addCategory($category);
        }

        return true;
    }

    private function updateImage(Game $game, array $data): bool
    {
        if (!isset($data['cover']['image_id']) || empty($data['cover']['image_id'])) {
            return false;
        }

        $imageUrl = $this->igdbApi->buildImageUrl($data['cover']['image_id'], 'original');
        $this->fileService->setUploadedFile($imageUrl, $game, 'imgFile');

        return true;
    }

    private function updateScreenshots(Game $game, array $data): bool
    {
        if (!isset($data['screenshots']) || !is_array($data['screenshots'])) {
            $game->setScreenshots([]);

            return true;
        }

        $screenshots = [];
        foreach ($data['screenshots'] as $result) {
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

        $videos  = [];
        foreach ($data['videos'] as $result) {
            if (is_null($result)) {
                continue;
            }

            $videos[] = $result['video_id'];
        }

        $game->setVideos($videos);

        return true;
    }
}
