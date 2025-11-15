<?php

namespace Labstag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Category;
use Labstag\Entity\Media;
use Labstag\Entity\Movie;
use Labstag\Entity\Post;
use Labstag\Entity\Saga;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Entity\Tag;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'labstag:regenerate:slug', description: 'Regenerate all entity slugs')]
class RegenerateSlugCommand extends Command
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
            Category::class,
            Media::class,
            Movie::class,
            Post::class,
            Saga::class,
            Serie::class,
            Story::class,
            Tag::class,
        ];

        foreach ($entities as $entity) {
            $symfonyStyle->section('Regenerating slugs for ' . $entity);

            $repository = $this->entityManager->getRepository($entity);
            $items      = $repository->findAll();

            $count = 0;
            foreach ($items as $item) {
                $title = $item->getTitle();
                $item->setTitle($title . ' ');
                $this->entityManager->persist($item);
                $item->setTitle(trim((string) $title));
                $this->entityManager->persist($item);

                ++$count;
            }

            if (0 < $count) {
                $this->entityManager->flush();
                $symfonyStyle->success(sprintf('✅ %d slugs regenerated for %s', $count, $entity));
            } else {
                $symfonyStyle->info('ℹ️  No slugs to regenerate for ' . $entity);
            }
        }

        $symfonyStyle->success('🎉 All slugs have been successfully regenerated!');

        return Command::SUCCESS;
    }
}
