<?php

declare(strict_types=1);

namespace App\Repository\Log;

use App\Entity\Log\CardioEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardioEntry>
 */
class CardioEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardioEntry::class);
    }
}
