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
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function __invoke(MetaMessage $metaMessage): void
    {
        $type = $metaMessage->getType();
        $entity = $metaMessage->getEntity();
        match ($type) {
            'delete' => $this->deleteUselessMeta(),
            'check' => $this->correctionMeta($entity),
            default => null,
        };
    }

    private function correctionMeta($entity): void
    {
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
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function deleteUselessMeta(): void
    {
        try {
            $repository      = $this->entityManager->getRepository(Meta::class);
            $metas                 = $repository->findAll();
            foreach ($metas as $meta) {
                $object   = $this->metaService->getEntityParent($meta);
                if (is_null($object->value) || is_null($object->name) || is_null($object)) {
                    $repository->delete($meta);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
