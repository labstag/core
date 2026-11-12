<?php

declare(strict_types=1);

namespace Labstag\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Labstag\Entity\Company;

/**
 * @extends RepositoryAbstract<Company>
 */
class CompanyRepository extends RepositoryAbstract
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }
}
