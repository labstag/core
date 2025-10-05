<?php

namespace Labstag\Controller\Admin\Extension;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use ReflectionClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * EasyAdmin extension applying restriction on entities having a refuser field/method
 * (ownership-based access control). Users only see their own entities,
 * except super-admins who see everything.
 */
final class OwnerRestrictionExtension
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, EntityDto $entityDto): void
    {
        $this->restrict($entityDto, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, EntityDto $entityDto): void
    {
        $this->restrict($entityDto, $queryBuilder);
    }

    private function restrict(EntityDto $entityDto, QueryBuilder $queryBuilder): void
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof \Symfony\Component\Security\Core\Authentication\Token\TokenInterface) {
            return;
        }

        $user = $token->getUser();
        if (!is_object($user)) {
            return;
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return;
            // pas de restriction
        }

        $fqcn = $entityDto->getFqcn();
        if (!class_exists($fqcn)) {
            return;
        }

        $reflectionClass        = new ReflectionClass($fqcn);
        $hasRefUser             = $reflectionClass->hasProperty('refuser') || $reflectionClass->hasMethod('getRefuser');
        if (!$hasRefUser) {
            return;
        }

        $aliases = $queryBuilder->getRootAliases();
        $alias   = $aliases[0] ?? 'entity';
        $queryBuilder->andWhere(sprintf('%s.refuser = :_ea_current_user', $alias));
        $queryBuilder->setParameter('_ea_current_user', $user);
    }
}
