<?php

namespace Labstag\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Labstag\Event\Abstract\EventEntityLib;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::postPersist)]
final class EntityListener extends EventEntityLib
{
    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $object        = $postPersistEventArgs->getObject();
        $entityManager = $postPersistEventArgs->getObjectManager();
        $this->postPersistMethods($object, $entityManager);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object        = $prePersistEventArgs->getObject();
        $entityManager = $prePersistEventArgs->getObjectManager();
        $this->prePersistMethods($object, $entityManager);
    }
}
