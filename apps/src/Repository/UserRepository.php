<?php

namespace Labstag\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\User;
use Labstag\Lib\ServiceEntityRepositoryLib;
use Override;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

class UserRepository extends ServiceEntityRepositoryLib implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function findUserName(string $field): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where(
            'u.username = :username OR u.email = :email'
        );
        $data = new ArrayCollection(
            [
                new Parameter('username', $field),
                new Parameter('email', $field),
            ]
        );
        $queryBuilder->setParameters($data);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    #[Override]
    public function upgradePassword(
        PasswordAuthenticatedUserInterface $passwordAuthenticatedUser,
        string $newHashedPassword
    ): void
    {
        if (!$passwordAuthenticatedUser instanceof User) {
            $message = sprintf(
                'Instances of "%s" are not supported.',
                $passwordAuthenticatedUser::class
            );

            throw new UnsupportedUserException($message);
        }

        $passwordAuthenticatedUser->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($passwordAuthenticatedUser);
        $this->getEntityManager()->flush();
    }
}
