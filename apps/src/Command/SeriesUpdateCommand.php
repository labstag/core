<?php

namespace Labstag\Command;

use Labstag\Repository\SerieRepository;
use Labstag\Service\SerieService;
use NumberFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:series:update', description: 'Update series description and image',)]
class SeriesUpdateCommand extends Command
{
    public function __construct(
        protected SerieRepository $serieRepository,
        protected SerieService $serieService,
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

        $update  = 0;
        $counter = 0;
        $this->serieService->deleteOldCategory();
        foreach ($series as $serie) {
            $status = $this->serieService->update($serie);
            $update = $status ? ++$update : $update;
            ++$counter;

            $this->serieRepository->persist($serie);
            $this->serieRepository->flush($counter);
            $progressBar->advance();
        }

        $this->serieRepository->flush();
        $this->serieService->deleteOldCategory();

        $progressBar->finish();

        $numberFormatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);
        $symfonyStyle->success(sprintf('Serie updated: %s', $numberFormatter->format($update)));

        return Command::SUCCESS;
    }
}
