<?php

namespace Labstag\Lib;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

abstract class ServiceEntityRepositoryLib extends ServiceEntityRepository
{
    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(object $entity): void
    {
        $this->remove($entity);
        $this->flush();
    }

    public function findDeleted()
    {
        $queryBuilder = $this->createQueryBuilder('entity');
        $queryBuilder->andWhere('entity.deletedAt IS NOT NULL');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function flush($counter = 0): void
    {
        $entityManager = $this->getEntityManager();
        if (0 == $counter || 0 == $counter % 20) {
            $entityManager->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(object $entity): void
    {
        $this->persist($entity);
        $this->flush();
    }
}
