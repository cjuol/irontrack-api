<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\PlannedExercise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlannedExercise>
 */
class PlannedExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlannedExercise::class);
    }
}
