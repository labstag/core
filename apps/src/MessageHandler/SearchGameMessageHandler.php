<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Game;
use Labstag\Message\AddGameMessage;
use Labstag\Message\SearchGameMessage;
use Labstag\Service\Igdb\GameService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SearchGameMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private GameService $gameService,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(SearchGameMessage $searchGameMessage): void
    {
        $data = $searchGameMessage->getData();
        $name = $data['Nom'] ?? null;
        $name = $data['name'] ?? $name;
        if (is_null($name) || $this->getGameByData($name) instanceof Game) {
            return;
        }

        $platform = $searchGameMessage->getPlatform();

        $result = $this->getResultApiForData($name);
        if (is_null($result)) {
            $this->logger->info(
                'Game not found',
                [
                    'data'     => $data,
                    'platform' => $platform,
                ]
            );

            return;
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

    private function getResultApiForData(string $name): ?array
    {
        $result = $this->gameService->getResultApiForData($name);
        if (!is_null($result)) {
            return $result;
        }

        $parts = preg_split('/\s*[-:]\s*/', $name);
        for ($i = count($parts) - 1; 0 < $i; --$i) {
            $shortenedName = implode(' - ', array_slice($parts, 0, $i));
            $result        = $this->gameService->getResultApiForData($shortenedName);
            if (!is_null($result)) {
                return $result;
            }
        }

        return null;
    }
}
