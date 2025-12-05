<?php

namespace Labstag\MessageHandler;

use Labstag\Generate\CinemaGenerate;
use Labstag\Message\PostVideoMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PostVideoMessageHandler
{
    public function __construct(
        private CinemaGenerate $cinemaGenerate,
    )
    {
    }

    public function __invoke(PostVideoMessage $postVideoMessage): void
    {
        dump('Creating page with movie releases of the week...');
        $this->cinemaGenerate->execute();
        dump('Page created with movie releases of the week.');
    }
}
