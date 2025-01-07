<?php

namespace Labstag\Lib;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class ServiceEntityRepositoryLib extends ServiceEntityRepository
{
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

    public function flush($counter = 0): void
    {
        $entityManager = $this->getEntityManager();
        if (0 == $counter || 0 == $counter % 20) {
            $entityManager->flush();
        }
    }

    public function persist(object $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function remove(object $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    public function save(object $entity): void
    {
        $this->persist($entity);
        $this->flush();
    }
}
