<?php

namespace Labstag\Service\Igdb;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Api\IgdbApi;
use Labstag\Entity\Game;
use Labstag\Entity\GameCategory;
use Labstag\Entity\Platform;
use Labstag\Service\CategoryService;
use Labstag\Service\FileService;
use Symfony\Component\HttpFoundation\Request;

final class GameService extends AbstractIgdb
{
    public function __construct(
        IgdbApi $igdbApi,
        EntityManagerInterface $entityManager,
        FileService $fileService,
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

    public function getGameApi(Request $request): array
    {
        $entityRepository     = $this->entityManager->getRepository(Game::class);
        $platformRepository = $this->entityManager->getRepository(Platform::class);
        $igbds              = $entityRepository->getAllIgdb();
        $all                = $request->query->all();
        $games              = [];
        if (isset($all['game'])) {
            $where  = '';
            $search = $all['game']['title'] ?? '';
            if (isset($all['game']['platform'])) {
                $platform = $platformRepository->find($all['game']['platform']);
                if ($platform instanceof Platform) {
                    if ('' !== $where) {
                        $where .= ' and ';
                    }

                    $where .= 'platforms = (' . $platform->getIgdb() . ')';
                }
            }

            if (isset($all['game']['number'])) {
                if ('' !== $where) {
                    $where .= ' and ';
                }

                $where .= ' id = ' . $all['game']['number'];
            }

            $body = $this->igdbApi->setBody(search: $search, where: $where, limit: 20);

            $games = $this->igdbApi->setUrl('games', $body);
            if (is_null($games)) {
                $games = [];
            }
        }

        return array_filter($games, fn (array $game): bool => !in_array($game['id'], $igbds));
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

    private function getApiGameArtworksIds(array $artworkIds): ?array
    {
        $body = $this->igdbApi->setBody(
            where: 'id = (' . implode(',', $artworkIds) . ')',
            limit: count($artworkIds)
        );

        $results = $this->igdbApi->setUrl('artworks', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGameCoverId(array $data): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $data['cover'], limit: 1);

        $results = $this->igdbApi->setUrl('covers', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGameId(string $id): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $id, limit: 1);

        $results = $this->igdbApi->setUrl('games', $body);
        if (is_null($results)) {
            return null;
        }

        return $results[0];
    }

    private function getApiGameScreenshotsIds(array $screenshotIds): ?array
    {
        $body = $this->igdbApi->setBody(
            where: 'id = (' . implode(',', $screenshotIds) . ')',
            limit: count($screenshotIds)
        );

        $results = $this->igdbApi->setUrl('screenshots', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGameVideosIds(array $artworkIds): ?array
    {
        $body = $this->igdbApi->setBody(
            where: 'id = (' . implode(',', $artworkIds) . ')',
            limit: count($artworkIds)
        );

        $results = $this->igdbApi->setUrl('game_videos', $body);
        if (is_null($results)) {
            return null;
        }

        return $results;
    }

    private function getApiGenreId(int $id): ?array
    {
        $body = $this->igdbApi->setBody(where: 'id = ' . $id, limit: 1);

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

        $datetime = new DateTime();
        $game->setReleaseDate($datetime->setTimestamp($data['first_release_date']));

        return $game;
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
        }

        if (!isset($data['franchises'])) {
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
