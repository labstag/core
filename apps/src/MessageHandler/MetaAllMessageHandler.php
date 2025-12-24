<?php

namespace Labstag\MessageHandler;

use Labstag\Entity\Game;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Person;
use Labstag\Entity\Post;
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Message\MetaAllMessage;
use Labstag\Message\MetaMessage;
use Labstag\Service\MessageDispatcherService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MetaAllMessageHandler
{
    public function __construct(
        private MessageDispatcherService $messageBus,
    )
    {

    }
    public function __invoke(MetaAllMessage $message): void
    {
        unset($message);

        $this->messageBus->dispatch(new MetaMessage('delete'));
        $entities = [
            Game::class,
            Movie::class,
            Page::class,
            Person::class,
            Post::class,
            Saga::class,
            Season::class,
            Serie::class,
            Story::class,
        ];
        foreach ($entities as $entity) {
            $this->messageBus->dispatch(new MetaMessage('check', $entity));
        }
    }
}
