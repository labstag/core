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
    public function delete($entity): void
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
        if ($counter == 0 || $counter % 20 == 0){
            $entityManager->flush();
            $entityManager->clear();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist($entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save($entity): void
    {
        $this->persist($entity);
        $this->flush();
    }
}
