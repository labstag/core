<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Labstag\Service\Imdb\MovieService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:movies:recommandations', description: 'Add a short description for your command',)]
class MoviesRecommandationsCommand extends Command
{
    public function __construct(
        private MovieService $movieService,
        private FileService $fileService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle              = new SymfonyStyle($input, $output);
        $recommandations           = $this->movieService->getAllRecommandations();

        $filename = 'recommandations-movie.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommandations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $symfonyStyle->success('Movie recommandations have been saved successfully.');

        return Command::SUCCESS;
    }
}
