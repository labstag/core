<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Labstag\Service\Imdb\MovieService;
use Labstag\Service\Imdb\SagaService;
use Labstag\Service\Imdb\SerieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:all:recommendations', description: 'Add a short description for your command',)]
class AllRecommendationsCommand extends Command
{
    public function __construct(
        private SerieService $serieService,
        private SagaService $sagaService,
        private MovieService $movieService,
        private FileService $fileService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle              = new SymfonyStyle($input, $output);
        $recommendations           = $this->movieService->getAllRecommendations();

        $filename = 'recommendations-movie.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommendations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $symfonyStyle->success('Movie recommendations have been saved successfully.');

        $recommendations           = $this->sagaService->getAllRecommendations();

        $filename = 'recommendations-saga.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommendations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $symfonyStyle->success('Saga recommendations have been saved successfully.');

        $recommendations           = $this->serieService->getAllRecommendations();

        $filename = 'recommendations-serie.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommendations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $symfonyStyle->success('Serie recommendations have been saved successfully.');

        return Command::SUCCESS;
    }
}
