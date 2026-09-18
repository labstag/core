<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Labstag\Event\Abstract\EventEntityLib;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class EntityListener extends EventEntityLib
{
    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object        = $prePersistEventArgs->getObject();
        $entityManager = $prePersistEventArgs->getObjectManager();
        $this->prePersistMethods($object, $entityManager);
    }

    public function preUpdate(PreUpdateEventArgs $preUpdateEventArgs): void
    {
        $object        = $preUpdateEventArgs->getObject();
        $entityManager = $preUpdateEventArgs->getObjectManager();
        $this->prePersistMethods($object, $entityManager);
    }
}
