<?php

namespace Labstag\Command;

use Labstag\Entity\Block;
use Labstag\Repository\BlockRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'correction:block:link', description: 'Add a short description for your command',)]
class CorrectionBlockLinkCommand extends Command
{
    public function __construct(
        protected BlockRepository $blockRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle     = new SymfonyStyle($input, $output);
        $blocks           = $this->blockRepository->findBy(
            ['type' => 'links']
        );

        $methods = get_class_methods(Block::class);
        if (!in_array('getLinks', $methods)) {
            $symfonyStyle->error('La méthode getLinks est manquante dans l\'entité Block');

            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output, count($blocks));
        $progressBar->start();

        foreach ($blocks as $block) {
            if (0 === count($block->getLinks())) {
                $progressBar->advance();
                continue;
            }

            $this->updateLinks($block);
            $this->blockRepository->save($block);

            $progressBar->advance();
        }

        $symfonyStyle->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    private function updateLinks(Block $block): void
    {
        $links = $block->getLinks();
        $data  = $block->getData();
        if (!isset($data['links'])) {
            $data['links'] = [];
        }

        foreach ($links as $tabLink) {
            $link = [
                'classes' => $tabLink->getClasses(),
                'title'   => $tabLink->getTitle(),
                'url'     => $tabLink->getUrl(),
                'blank'   => $tabLink->isBlank(),
            ];
            $data['links'][] = $link;
            $block->removeLink($tabLink);
        }

        $block->setData($data);
    }
}
