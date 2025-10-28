<?php

namespace Labstag\Command;

use Labstag\Repository\MetaRepository;
use Labstag\Service\MetaService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'labstag:deleteoldmeta',
    description: 'Add a short description for your command',
)]
class DeleteOldMetaCommand extends Command
{
    public function __construct(
        protected MetaService $metaService,
        protected MetaRepository $metaRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $metas = $this->metaRepository->findAll();
        foreach($metas as $meta) {
            $object   = $this->metaService->getEntityParent($meta);
            if (is_null($object->value) || is_null($object->name) || is_null($object)) {
                $this->metaRepository->delete($meta);
            }
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
