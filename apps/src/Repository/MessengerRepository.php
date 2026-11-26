<?php

declare(strict_types=1);

namespace Labstag\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Messenger;

class MessengerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messenger::class);
    }

    public function cleanupExpired(int $batch = 500): int
    {
        $total = 0;
        do {
            $ids = $this->createQueryBuilder('m')->select('m.id')->where('m.status = :s')->andWhere('m.expiresAt IS NOT NULL')->andWhere('m.expiresAt < :now')->setParameter('s', Messenger::STATUS_SUCCESS)->setParameter('now', new DateTimeImmutable())->setMaxResults($batch)->getQuery()->getSingleColumnResult();

            if ([] === $ids) {
                break;
            }

            $affected = $this->createQueryBuilder('m')->delete()->where('m.id IN (:ids)')->setParameter('ids', $ids)->getQuery()->execute();

            $total += $affected;
        } while (count($ids) >= $batch);

        return $total;
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('m')->select('m.status AS status, COUNT(m.id) AS c')->groupBy('m.status')->getQuery()->getArrayResult();
        $out  = [];
        foreach ($rows as $row) {
            $out[$row['status']] = (int) $row['c'];
        }

        return $out;
    }

    public function countExpired(): int
    {
        return (int) $this->createQueryBuilder('m')->select('COUNT(m.id)')->where('m.status = :s')->andWhere('m.expiresAt IS NOT NULL')->andWhere('m.expiresAt < :now')->setParameter('s', Messenger::STATUS_SUCCESS)->setParameter('now', new DateTimeImmutable())->getQuery()->getSingleScalarResult();
    }

    public function findByCorrelationId(string $correlationId): ?Messenger
    {
        return $this->findOneBy(
            ['correlationId' => $correlationId]
        );
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(
            ['status' => $status]
        );
    }
}
