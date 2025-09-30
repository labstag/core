<?php

namespace Labstag\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class CrudAdminService
{
    public function __construct(
        #[AutowireIterator(tag: 'labstag.admincontroller')]
        private iterable $controllers,
    )
    {
    }

    public function getCrudAdmin(string $entity): ?string
    {
        foreach ($this->controllers as $controller) {
            $entityClass = $controller->getEntityFqcn();
            // dump(get_class_methods($controller));
            if ($entityClass == $entity) {
                return $controller::class;
            }
        }

        return null;
    }
}
