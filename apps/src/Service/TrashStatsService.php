<?php

declare(strict_types=1);

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use ReflectionClass;
use Symfony\Component\Translation\TranslatableMessage;

final class TrashStatsService
{
    /**
     * @param array<string, string> $controllers
     *
     * @return array<int, array{label: TranslatableMessage, count: int, url: string}>
     */
    public function getTrashItems(array $controllers, EntityManagerInterface $entityManager, AdminUrlGenerator $urlGenerator): array
    {
        $trash   = [];
        $filters = $entityManager->getFilters();

        foreach ($controllers as $label => $controller) {
            $fqcn            = $controller::getEntityFqcn();
            $reflectionClass = new ReflectionClass($fqcn);
            if (!$reflectionClass->hasMethod('getDeletedAt')) {
                continue;
            }

            $repository = $entityManager->getRepository($fqcn);
            if (!method_exists($repository, 'findDeleted')) {
                continue;
            }

            if ($filters->has('softdeleteable') && $filters->isEnabled('softdeleteable')) {
                $filters->disable('softdeleteable');
            }

            $deleted = $repository->findDeleted();
            if ($filters->has('softdeleteable') && !$filters->isEnabled('softdeleteable')) {
                $filters->enable('softdeleteable');
            }

            $count = is_array($deleted) ? count($deleted) : 0;
            if (0 === $count) {
                continue;
            }

            $url = $urlGenerator->setController($controller)->setAction('index')->set('action', 'trash')->generateUrl();

            $trash[] = [
                'label' => $label,
                'count' => $count,
                'url'   => $url,
            ];
        }

        return $trash;
    }
}
