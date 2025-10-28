<?php

namespace Labstag\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class CrudAdminService
{
    public function __construct(
        /**
         * @var iterable<\Labstag\Controller\AdminCrudControllerAbstract>
         */
        #[AutowireIterator('labstag.admincontroller')]
        private readonly iterable $controllers,
    )
    {
    }

    public function getCrudAdmin(string $entity): ?string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            if ($entityClass == $entity) {
                return $controller::class;
            }
        }

        return null;
    }
}
