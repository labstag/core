<?php

namespace Labstag\Command;

use Labstag\Entity\Movie;
use Labstag\Repository\MovieRepository;
use Labstag\Service\MovieService;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:movies-update', description: 'Update movie description and image',)]
class MoviesUpdateCommand extends Command
{
    public function __construct(
        protected MovieRepository $movieRepository,
        protected MovieService $movieService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $this->updateImage($output, $symfonyStyle);
        $this->updateDescription($output, $symfonyStyle);

        return Command::SUCCESS;
    }

    private function updateImage(OutputInterface $output, SymfonyStyle $symfonyStyle): void
    {
        // Movie without img
        $movies = $this->movieRepository->findall();

        $progressBar = new ProgressBar($output, count($movies));
        $progressBar->start();

        $update  = 0;
        $counter = 0;
        foreach ($movies as $movie) {
            $status = $this->movieService->updateImage($movie);
            $update = $status ? ++$update : $update;
            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
            $progressBar->advance();
        }

        $this->movieRepository->flush();
        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Images updated: %d', $numberFormatter->format($update)));
    }

    private function updateDescription(OutputInterface $output, SymfonyStyle $symfonyStyle): void
    {
        // Movie without img
        $movies = $this->movieRepository->findBy(
            ['description' => null]
        );

        $progressBar = new ProgressBar($output, count($movies));
        $progressBar->start();

        $update  = 0;
        $counter = 0;
        foreach ($movies as $movie) {
            $status = $this->movieService->updateDescription($movie);
            $update = $status ? ++$update : $update;
            ++$counter;

            $this->movieRepository->persist($movie);
            $this->movieRepository->flush($counter);
            $progressBar->advance();
        }

        $this->movieRepository->flush();
        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Descriptions updated: %d', $numberFormatter->format($update)));
    }
}
