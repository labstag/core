<?php

namespace Labstag\Lib\Admin\Extension;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
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
        private AdminContextProvider $adminContextProvider,
    ) {
    }

    public function applyToCollection(QueryBuilder $qb, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): void
    {
        $this->restrict($entityDto, $qb);
    }

    public function applyToItem(QueryBuilder $qb, EntityDto $entityDto, KeyValueStore $keyValueStore): void
    {
        $this->restrict($entityDto, $qb);
    }

    private function restrict(EntityDto $entityDto, QueryBuilder $qb): void
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }
        $user = $token->getUser();
        if (!is_object($user) || !method_exists($user, 'getRoles')) {
            return;
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return; // pas de restriction
        }

        $fqcn = $entityDto->getFqcn();
        if (!class_exists($fqcn)) {
            return;
        }

        $ref = new \ReflectionClass($fqcn);
        $hasRefUser = $ref->hasProperty('refuser') || $ref->hasMethod('getRefuser');
        if (!$hasRefUser) {
            return;
        }

        $aliases = $qb->getRootAliases();
        $alias   = $aliases[0] ?? 'entity';
        $qb->andWhere(sprintf('%s.refuser = :_ea_current_user', $alias));
        $qb->setParameter('_ea_current_user', $user);
    }
}
