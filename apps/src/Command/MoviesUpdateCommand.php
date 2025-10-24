<?php

namespace Labstag\Command;

use Labstag\Message\MovieMessage;
use Labstag\Repository\MovieRepository;
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
        protected MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $movies       = $this->movieRepository->findAllUpdate();

        $progressBar = new ProgressBar($output, count($movies));
        $progressBar->start();
        foreach ($movies as $movie) {
            $this->messageBus->dispatch(new MovieMessage($movie->getId()));
            $progressBar->advance();
        }

        $this->movieRepository->flush();

        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Movie updated: %s', $numberFormatter->format(count($movies))));

        return Command::SUCCESS;
    }
}
