<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\WeeklyMetabolicPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeeklyMetabolicPlan>
 */
class WeeklyMetabolicPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeeklyMetabolicPlan::class);
    }
}
