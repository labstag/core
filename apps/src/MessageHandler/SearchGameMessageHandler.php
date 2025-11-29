<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\IgdbApi;
use Labstag\Entity\Game;
use Labstag\Message\AddGameMessage;
use Labstag\Message\SearchGameMessage;
use Labstag\Service\Igdb\GameService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SearchGameMessageHandler
{
    public function __construct(
        private IgdbApi $igdbApi,
        private MessageBusInterface $messageBus,
        private GameService $gameService,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(SearchGameMessage $searchGameMessage): void
    {
        $data = $searchGameMessage->getData();
        $name = trim((string) $data['Nom']);
        if ($this->getGameByData($data) instanceof Game) {
            return;
        }

        $platform = $searchGameMessage->getPlatform();

        $result = $this->gameService->getResultApiForData($data);
        if (is_null($result)) {
            return;
        }
        
        $this->messageBus->dispatch(new AddGameMessage($result['id'], 'game', $platform));
    }

    private function getGameByData(array $row): ?Game
    {
        $entityRepository = $this->entityManager->getRepository(Game::class);

        return $entityRepository->findOneBy(
            [
                'title' => $row['Nom'],
            ]
        );
    }
}
