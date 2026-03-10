<?php

declare(strict_types=1);

namespace App\Repository\Program;

use App\Entity\Program\ExerciseBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExerciseBlock>
 */
class ExerciseBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExerciseBlock::class);
    }
}
