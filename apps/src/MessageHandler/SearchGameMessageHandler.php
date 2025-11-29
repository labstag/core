<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Api\IgdbApi;
use Labstag\Entity\Game;
use Labstag\Message\AddGameMessage;
use Labstag\Message\SearchGameMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class SearchGameMessageHandler
{
    public function __construct(
        private IgdbApi $igdbApi,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(SearchGameMessage $searchGameMessage): void
    {
        $data = $searchGameMessage->getData();
        if ($this->getGameByData($data) instanceof Game) {
            return;
        }

        $platform = $searchGameMessage->getPlatform();

        $body    = $this->igdbApi->setBody(search: $data['Nom'], fields: ['*', 'cover.*', 'game_type.*']);
        $results = $this->igdbApi->setUrl('games', $body);
        if (is_null($results)) {
            return;
        }

        if (0 === count($results)) {
            return;
        }

        if (1 === count($results)) {
            $this->messageBus->dispatch(new AddGameMessage($results[0]['id'], 'game', $platform));
        }

        foreach ($results as $result) {
            if ($result['name'] == $data['Nom']) {
                $this->messageBus->dispatch(new AddGameMessage($result['id'], 'game', $platform));

                break;
            }
        }
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
