<?php

namespace Labstag\Command;

use Labstag\Message\PageCinemaMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'labstag:page-cinema',
    description: 'Add a short description for your command',
)]
class PageCinemaCommand extends Command
{
    public function __construct(
        protected MessageBusInterface $messageBus
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->messageBus->dispatch(new PageCinemaMessage());

        $io->success('Cinema pages generation launched');

        return Command::SUCCESS;
    }
}
