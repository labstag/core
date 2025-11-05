<?php

namespace Labstag\Command;

use Labstag\Message\SerieMessage;
use Labstag\Repository\SerieRepository;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:series:update', description: 'Update series description and image',)]
class SeriesUpdateCommand extends Command
{
    public function __construct(
        protected SerieRepository $serieRepository,
        protected MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $series       = $this->serieRepository->findAllUpdate();

        $progressBar = new ProgressBar($output, count($series));
        $progressBar->start();
        foreach ($series as $serie) {
            $this->messageBus->dispatch(new SerieMessage($serie->getId()));
            $progressBar->advance();
        }

        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Serie updated: %s', $numberFormatter->format(count($series))));

        return Command::SUCCESS;
    }
}
