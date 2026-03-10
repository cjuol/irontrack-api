<?php

declare(strict_types=1);

namespace App\Repository\Catalog;

use App\Entity\Catalog\MuscleGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MuscleGroup>
 */
class MuscleGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MuscleGroup::class);
    }

    /**
     * Devuelve todos los grupos musculares ordenados alfabéticamente.
     *
     * @return MuscleGroup[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('mg')
            ->orderBy('mg.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?MuscleGroup
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
