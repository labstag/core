<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

final class VichListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[AsEventListener(event: Events::PRE_REMOVE)]
    public function preRemove(Event $event)
    {
        $filterCollection = $this->entityManager->getFilters();
        $object           = $event->getObject();
        $enable           = $this->isDeletedFileNotEntity($object);
        if ($filterCollection->isEnabled('deletedfile') || $enable) {
            return;
        }

        $event->cancel();
    }

    private function isDeletedFileNotEntity($entity): bool
    {
        $delete           = false;
        $reflectionClass  = $this->setReflection($entity);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $name  = $reflectionProperty->getName();
            $value = $propertyAccessor->getValue($entity, $name);
            if (!$value instanceof UploadedFile || is_null($value)) {
                continue;
            }

            $delete = true;

            break;
        }

        return $delete;
    }

    private function setReflection($entity): ReflectionClass
    {
        return new ReflectionClass($entity);
    }
}
