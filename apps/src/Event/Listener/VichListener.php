<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

final class VichListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[AsEventListener(event: Events::PRE_REMOVE)]
    public function preRemove(Event $event): void
    {
        $filterCollection = $this->entityManager->getFilters();
        $object = $event->getObject();
        $enable = $this->isDeletedFileNotEntity($object);
        if ($filterCollection->isEnabled('deletedfile') || $enable) {
            return;
        }

        $event->cancel();
    }

    private function isDeletedFileNotEntity(object $entity): bool
    {
        $delete = false;
        $reflectionClass = new ReflectionClass($entity);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            $value = $propertyAccessor->getValue($entity, $name);
            if (!$value instanceof UploadedFile) {
                continue;
            }

            $delete = true;

            break;
        }

        return $delete;
    }
}
