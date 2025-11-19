<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Labstag\Service\Imdb\SagaService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:saga:recommandations', description: 'Add a short description for your command',)]
class SagaRecommandationsCommand extends Command
{
    public function __construct(
        private SagaService $sagaService,
        private FileService $fileService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle                        = new SymfonyStyle($input, $output);
        $recommandations           = $this->sagaService->getAllRecommandations();

        $filename = 'recommandations-saga.json';
        $this->fileService->saveFileInAdapter(
            'private',
            $filename,
            json_encode($recommandations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $symfonyStyle->success('Saga recommandations have been saved successfully.');

        return Command::SUCCESS;
    }
}
