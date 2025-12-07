<?php

namespace Labstag\MessageHandler;

use Labstag\Generate\CinemaGenerate;
use Labstag\Message\PageCinemaMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PageCinemaMessageHandler
{
    public function __construct(
        private CinemaGenerate $cinemaGenerate,
    )
    {
    }

    public function __invoke(PageCinemaMessage $pageCinemaMessage): void
    {
        unset($pageCinemaMessage);
        dump('Creating page with movie releases of the week...');
        $this->cinemaGenerate->execute();
        dump('Page created with movie releases of the week.');
    }
}
