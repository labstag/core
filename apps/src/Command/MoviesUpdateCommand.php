<?php

namespace Labstag\Command;

use Exception;
use Labstag\Entity\Movie;
use Labstag\Repository\MovieRepository;
use Labstag\Service\ImdbService;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(
    name: 'labstag:movies-update',
    description: 'Add a short description for your command',
)]
class MoviesUpdateCommand extends Command
{

    public function __construct(
        protected MovieRepository $movieRepository,
        protected ImdbService $imdbService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        // Movie without img
        $movies = $this->movieRepository->findBy(['img' => null]);

        $progressBar = new ProgressBar($output, count($movies));
        $progressBar->start();
        $update = 0;
        $counter = 0;
        foreach ($movies as $movie) {
            $status = $this->imdbService->update($movie);
            $counter = $status ? ++$update : $update;
            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
            $progressBar->advance();
        }

        $this->movieRepository->flush();
        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(
            sprintf(
                'Updated: %d',
                $numberFormatter->format($update)
            )
        );

        return Command::SUCCESS;
    }
}
