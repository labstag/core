<?php

namespace Labstag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\LinksBlock;
use Labstag\Repository\BlockRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'correction:block:link', description: 'Add a short description for your command',)]
class CorrectionBlockLinkCommand
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected BlockRepository $blockRepository,
    )
    {
    }

    public function __invoke(SymfonyStyle $symfonyStyle, OutputInterface $output): int
    {
        $blocks           = $this->entityManager->getRepository(LinksBlock::class)->findAll();
        $methods          = get_class_methods(LinksBlock::class);
        if (!in_array('getLinks', $methods)) {
            $symfonyStyle->error('La méthode getLinks est manquante dans l\'entité LinksBlock');

            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output, count($blocks));
        $progressBar->start();

        foreach ($blocks as $block) {
            $this->updateLinks($block);
            $this->blockRepository->save($block);

            $progressBar->advance();
        }

        $symfonyStyle->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    private function updateLinks(LinksBlock $linksBlock): void
    {
        $data = $linksBlock->getData();
        $linksBlock->setLinks($data['links'] ?? []);
    }
}
