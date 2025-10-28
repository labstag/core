<?php

namespace Labstag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Event\Events;

final class VichListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
    )
    {
    }

    #[AsEventListener(event: Events::PRE_REMOVE)]
    public function preRemove(Event $event): void
    {
        $filterCollection = $this->entityManager->getFilters();
        $object           = $event->getObject();

        // Si le filtre deletedfile est activé, on autorise toujours la suppression
        if ($filterCollection->isEnabled('deletedfile')
        ) {
            return;
        }

        // Si c'est un nouveau fichier uploadé, on autorise la suppression
        if ($this->isDeletedFileNotEntity($object)
        ) {
            return;
        }

        // Si c'est une requête admin, on autorise la suppression (simplifié)
        if ($this->isAdminRequest()) {
            return;
        }

        // Sinon, on annule la suppression pour préserver les fichiers existants
        $event->cancel();
    }

    private function isAdminRequest(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof \Symfony\Component\HttpFoundation\Request) {
            return false;
        }

        // Vérifier si c'est une requête admin
        $pathInfo = $request->getPathInfo();

        return str_contains($pathInfo, '/admin/');
    }

    private function isDeletedFileNotEntity(object $entity): bool
    {
        $delete           = false;
        $reflectionClass  = new ReflectionClass($entity);
        $properties       = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $name  = $property->getName();
            $type  = $property->getType();
            if ('lazyObjectState' == $name) {
                continue;
            }

            if (is_null($type)) {
                continue;
            }

            if (!$type->getName() instanceof UploadedFile) {
                continue;
            }

            $delete = true;

            break;
        }

        return $delete;
    }
}
