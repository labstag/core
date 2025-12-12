<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Game;
use Labstag\Entity\Platform;
use Labstag\Message\AddGameMessage;
use Labstag\Message\SearchGameMessage;
use Labstag\Service\Igdb\GameService;
use Labstag\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SearchGameMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private GameService $gameService,
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(SearchGameMessage $searchGameMessage): void
    {
        $data = $searchGameMessage->getData();
        $name = $data['Nom'] ?? $data['name'] ?? null;
        if (is_null($name) || $this->getGameByData($name) instanceof Game) {
            return;
        }

        $platform = $searchGameMessage->getPlatform();
        $result   = $this->getResultApiForData($data, $platform);
        if (is_null($result)) {
            $this->notificationService->setNotification(
                'Game not found',
                sprintf('The game %s was not found on IGDB', $name)
            );

            return;
        }

        $game = $this->getGameByRow($result);
        if ($game instanceof Game) {
            $this->notificationService->setNotification(
                'Game found',
                sprintf('The game "%s" was found on IGDB with the name "%s"', $name, $result['name'])
            );
        }

        $this->messageBus->dispatch(new AddGameMessage($result['id'], 'game', $platform));
    }

    private function getGameByData(string $name): ?Game
    {
        $entityRepository = $this->entityManager->getRepository(Game::class);

        return $entityRepository->findOneBy(
            ['title' => $name]
        );
    }

    private function getGameByRow(array $data): ?Game
    {
        $entityRepository = $this->entityManager->getRepository(Game::class);
        
        
        return $entityRepository->findOneBy(
            [
                'igdb' => $data['id'],
            ]
        );
    }

    private function getResultApiForData(array $data, string $platformId): ?array
    {
        $entityRepository = $this->entityManager->getRepository(Platform::class);
        $platform         = $entityRepository->find($platformId);
        $result           = $this->gameService->getResultApiForDataArray($data, $platform);
        if (!is_null($result)) {
            return $result;
        }

        return null;
    }
}
