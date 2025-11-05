<?php

namespace Labstag\Command;

use Labstag\Repository\MetaRepository;
use Labstag\Service\MetaService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:deleteoldmeta', description: 'Add a short description for your command',)]
class DeleteOldMetaCommand extends Command
{
    public function __construct(
        protected MetaService $metaService,
        protected MetaRepository $metaRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle    = new SymfonyStyle($input, $output);
        $metas           = $this->metaRepository->findAll();

        $progressBar = new ProgressBar($output, count($metas));
        $progressBar->start();
        foreach ($metas as $meta) {
            $object   = $this->metaService->getEntityParent($meta);
            if (is_null($object->value) || is_null($object->name) || is_null($object)) {
                $this->metaRepository->delete($meta);
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $symfonyStyle->success('All old metadata have been deleted.');

        return Command::SUCCESS;
    }
}
