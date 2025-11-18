<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Labstag\Service\Imdb\SerieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:series:recommandations', description: 'Add a short description for your command',)]
class SeriesRecommandationsCommand extends Command
{
    public function __construct(
        private SerieService $serieService,
        private FileService $fileService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle              = new SymfonyStyle($input, $output);
        $recommandations = $this->serieService->getAllRecommandations();

        $filename = 'recommandations-serie.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommandations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $symfonyStyle->success('Serie recommandations have been saved successfully.');

        return Command::SUCCESS;
    }
}
