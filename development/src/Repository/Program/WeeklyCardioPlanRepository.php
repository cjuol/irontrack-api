<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\WeeklyCardioPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeeklyCardioPlan>
 */
class WeeklyCardioPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyCardioPlan::class);
    }
}
