<?php

namespace Labstag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Game;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Post;
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:regenerate:meta', description: 'Regenerate all entity metas')]
class RegenerateMetaCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $entities = [
            Game::class,
            Movie::class,
            Page::class,
            Post::class,
            Saga::class,
            Season::class,
            Serie::class,
            Story::class,
        ];

        foreach ($entities as $entity) {
            $symfonyStyle->section('Regenerating slugs for ' . $entity);

            $repository = $this->entityManager->getRepository($entity);
            $items      = $repository->findAll();

            $count = 0;
            foreach ($items as $item) {
                $meta = $item->getMeta();
                if (!$meta instanceof Meta) {
                    continue;
                }

                $meta = new Meta();
                $item->setMeta($meta);
                $this->entityManager->persist($item);

                ++$count;
            }

            if (0 < $count) {
                $this->entityManager->flush();
                $symfonyStyle->success(sprintf('✅ %d metas regenerated for %s', $count, $entity));
                continue;
            }

            $symfonyStyle->info('ℹ️  No metas to regenerate for ' . $entity);
        }

        $symfonyStyle->success('🎉 All metas have been successfully regenerated!');

        return Command::SUCCESS;
    }
}
