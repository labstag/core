<?php

namespace Labstag\Command;

use Labstag\Service\FileService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:delete-oldfiles', description: 'Delete old files')]
class DeleteOldFilesCommand
{
    public function __construct(
        protected FileService $fileService,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle): int
    {
        $total = $this->fileService->deletedFileByEntities();
        if (0 !== $total) {
            $symfonyStyle->writeln($total . ' file(s) deleted');
        }

        $symfonyStyle->writeln('Script executed successfully.');

        return Command::SUCCESS;
    }
}
