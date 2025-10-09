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

        $this->updateEntityParagraph($object);
        $this->updateEntityBlock($object);
        $this->updateEntityBanIp($object, $entityManager);
        $this->updateEntityStory($object);
        $this->updateEntityMovie($object);
        $this->updateEntityChapter($object);
        $this->updateEntityPage($object);
        $this->updateEntityRedirection($object);

        $entityManager->flush();
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $object = $prePersistEventArgs->getObject();
        $prePersistEventArgs->getObjectManager();
        $this->initworkflow($object);
        $this->updateEntityPage($object);
        $this->initEntityMeta($object);
    }
}
