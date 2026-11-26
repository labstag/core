<?php

namespace Labstag\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Messenger;

class MessengerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messenger::class);
    }

    public function countExpired(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.status = :s')
            ->andWhere('m.expiresAt IS NOT NULL')
            ->andWhere('m.expiresAt < :now')
            ->setParameter('s', Messenger::STATUS_SUCCESS)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function cleanupExpired(int $batch = 500): int
    {
        $qb = $this->createQueryBuilder('m')
            ->delete()
            ->where('m.status = :s')
            ->andWhere('m.expiresAt IS NOT NULL')
            ->andWhere('m.expiresAt < :now')
            ->setParameter('s', Messenger::STATUS_SUCCESS)
            ->setParameter('now', new \DateTimeImmutable());

        $total = 0;
        do {
            $qb->setMaxResults($batch);
            $affected = $qb->getQuery()->execute();
            $total += $affected;
        } while ($affected >= $batch);

        return $total;
    }
}