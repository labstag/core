<?php

namespace Labstag\Service\Igdb;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\IgdbApi;
use Labstag\Entity\Game;
use Labstag\Entity\Platform;
use Labstag\Service\FileService;
use Labstag\Service\Igdb\Game\GameApiService;
use Labstag\Service\Igdb\Game\GameUpdateService;

final class GameService extends AbstractIgdb
{
    public function __construct(
        IgdbApi $igdbApi,
        EntityManagerInterface $entityManager,
        FileService $fileService,
        private GameUpdateService $gameUpdateService,
        private GameApiService $gameApiService,
    )
    {
        parent::__construct($igdbApi, $entityManager, $fileService);
    }

    public function addByApi(string $id, $platformId): ?Game
    {
        $result = $this->gameApiService->getApiGameId($id);
        if (is_null($result)) {
            return null;
        }

        $game = $this->gameApiService->getGameByRow($result);
        $this->update($game);
        $this->entityManager->getRepository(Game::class)->save($game);
        $platform = $this->entityManager->getRepository(Platform::class)->find($platformId);
        if ($platform instanceof Platform) {
            $game->addPlatform($platform);
        }

        $this->entityManager->getRepository(Game::class)->save($game);

        return $game;
    }

    public function update(Game $game): bool
    {
        $result = $this->gameApiService->getApiGameId($game->getIgdb() ?? '0');
        if (0 == count($result)) {
            return false;
        }

        $statuses = [
            $this->gameUpdateService->updateGame($game, $result),
            $this->gameUpdateService->updateImage($game, $result),
            $this->gameUpdateService->updateFranchises($game, $result),
            $this->gameUpdateService->updateScreenshots($game, $result),
            $this->gameUpdateService->updateArtworks($game, $result),
            $this->gameUpdateService->updateVideos($game, $result),
            $this->gameUpdateService->updateGenres($game, $result),
        ];

        return in_array(true, $statuses, true);
    }

}
