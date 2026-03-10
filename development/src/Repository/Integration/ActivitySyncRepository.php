<?php

declare(strict_types=1);

namespace App\Repository\Integration;

use App\Entity\Integration\ActivitySync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivitySync>
 */
class ActivitySyncRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivitySync::class);
    }
}
