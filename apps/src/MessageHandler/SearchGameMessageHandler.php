<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Game;
use Labstag\Entity\Platform;
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
        $result   = $this->getResultApiForData($data, $platform);
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

    private function getResultApiForData(array $data, string $platformId): ?array
    {
        $name   = $data['Nom'] ?? null;
        $name   = $data['name'] ?? $name;

        $repository = $this->entityManager->getRepository(Platform::class);
        $platform   = $repository->find($platformId);

        $result = $this->gameService->getResultApiForDataArray($data, $platform, false);
        if (!is_null($result)) {
            return $result;
        }

        $result = $this->gameService->getResultApiForDataArray($data, $platform, true);
        if (!is_null($result)) {
            return $result;
        }

        return null;
    }
}
