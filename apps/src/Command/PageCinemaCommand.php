<?php

namespace Labstag\Command;

use Labstag\Message\PageCinemaMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:page-cinema', description: 'Add a short description for your command',)]
class PageCinemaCommand
{
    public function __construct(
        protected MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle): int
    {
        $this->messageBus->dispatch(new PageCinemaMessage());

        $symfonyStyle->success('Cinema pages generation launched');

        return Command::SUCCESS;
    }
}
