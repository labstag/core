<?php

namespace Labstag\Command;

use Labstag\Message\MovieMessage;
use Labstag\Message\SagaMessage;
use Labstag\Repository\MovieRepository;
use Labstag\Repository\SagaRepository;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'labstag:movies:update', description: 'Update movie description and image',)]
class MoviesUpdateCommand extends Command
{
    public function __construct(
        protected MovieRepository $movieRepository,
        protected SagaRepository $sagaRepository,
        protected MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $movies       = $this->movieRepository->findAllUpdate();
        $sagas        = $this->sagaRepository->findAll();
        $progressBar  = new ProgressBar($output, count($movies) + count($sagas));
        $progressBar->start();
        foreach ($movies as $movie) {
            $this->messageBus->dispatch(new MovieMessage($movie->getId()));
            $progressBar->advance();
        }

        foreach ($sagas as $saga) {
            $this->messageBus->dispatch(new SagaMessage($saga->getId()));
            $progressBar->advance();
        }

        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Movie updated: %s', $numberFormatter->format(count($movies))));
        $symfonyStyle->success(sprintf('Saga updated: %s', $numberFormatter->format(count($sagas))));

        return Command::SUCCESS;
    }
}
