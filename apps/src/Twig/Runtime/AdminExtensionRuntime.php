<?php

namespace Labstag\Twig\Runtime;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Twig\Extension\RuntimeExtensionInterface;

class AdminExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
        private AdminUrlGenerator $adminUrlGenerator,
    )
    {
    }

    public function url(string $type, object $entity): string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $entity::class) {
                $url = $this->adminUrlGenerator->setController($controller::class);
                $url->setAction($type);
                $url->setEntityId($entity->getId());

                return $url->generateUrl();
            }
        }

        return '';
    }
}
