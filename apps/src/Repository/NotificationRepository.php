<?php

namespace Labstag\Repository;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Notification;

/**
 * @extends ServiceEntityReRepositoryAbstractpository<Notification>
 */
class NotificationRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function getAllBefore1week()
    {
        $qb = $this->createQueryBuilder('n');
        $qb->where('n.createdAt <= :date');
        $qb->setParameter('date', new DateTime('-7 days'));

        return $qb->getQuery()->getResult();
    }
}
