<?php

namespace Labstag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @template TEntityClass of object
 *
 * @extends ServiceEntityRepository<TEntityClass>
 */
abstract class ServiceEntityRepositoryAbstract extends ServiceEntityRepository
{
    public function delete(object $entity): void
    {
        $this->remove($entity);
        $this->flush();
    }

    public function findDeleted(): mixed
    {
        $queryBuilder = $this->createQueryBuilder('entity');
        $queryBuilder->andWhere('entity.deletedAt IS NOT NULL');

        $query = $queryBuilder->getQuery();
        $query->enableResultCache(3600, 'service-find_deleted');

        return $query->getResult();
    }

    public function flush(int $counter = 0): void
    {
        $entityManager = $this->getEntityManager();
        if (0 === $counter || 0 === $counter % 20) {
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
