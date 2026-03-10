<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\PlannedSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlannedSet>
 */
class PlannedSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlannedSet::class);
    }
}
