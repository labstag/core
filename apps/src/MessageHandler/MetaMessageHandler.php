<?php

namespace Labstag\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Labstag\Entity\Meta;
use Labstag\Message\MetaMessage;
use Labstag\Service\MetaService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MetaMessageHandler
{
    public function __construct(
        private MetaService $metaService,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function __invoke(MetaMessage $metaMessage): void
    {
        $entity = $metaMessage->getEntity();
        try {
            $repository = $this->entityManager->getRepository($entity);
            $items      = $repository->findAll();
            foreach ($items as $item) {
                $meta = $item->getMeta();
                if (!$meta instanceof Meta) {
                    continue;
                }

                $meta = new Meta();
                $item->setMeta($meta);
                $repository->save($item);
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}
