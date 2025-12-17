<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Game;
use Labstag\Entity\Meta;
use Labstag\Entity\Movie;
use Labstag\Entity\Page;
use Labstag\Entity\Person;
use Labstag\Entity\Post;
use Labstag\Entity\Saga;
use Labstag\Entity\Season;
use Labstag\Entity\Serie;
use Labstag\Entity\Story;
use Labstag\Message\MetaMessage;
use Labstag\Repository\MetaRepository;
use Labstag\Service\MetaService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MetaMessageHandler
{
    public function __construct(
        private MetaService $metaService,
        private EntityManagerInterface $entityManager,
        private MetaRepository $metaRepository,
    )
    {
    }

    public function __invoke(MetaMessage $metaMessage): void
    {
        unset($metaMessage);
        $this->deleteUselessMeta();
        $this->correctionMeta();
    }

    private function correctionMeta(): void
    {
        $entities = [
            Game::class,
            Movie::class,
            Page::class,
            Person::class,
            Post::class,
            Saga::class,
            Season::class,
            Serie::class,
            Story::class,
        ];

        foreach ($entities as $entity) {
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
                continue;
            }
        }
    }

    private function deleteUselessMeta(): void
    {
        $entityRepository      = $this->entityManager->getRepository(Meta::class);
        $metas                 = $entityRepository->findAll();
        foreach ($metas as $meta) {
            $object   = $this->metaService->getEntityParent($meta);
            if (is_null($object->value) || is_null($object->name) || is_null($object)) {
                $entityRepository->delete($meta);
            }
        }
    }
}
