<?php

declare(strict_types=1);

namespace App\Repository\Catalog;

use App\Entity\Catalog\Exercise;
use App\Entity\Catalog\MuscleGroup;
use App\Enum\EquipmentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exercise>
 */
class ExerciseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercise::class);
    }

    /**
     * Búsqueda de ejercicios con filtros opcionales.
     * Usado por el endpoint GET /api/v1/exercises
     *
     * @return Exercise[]
     */
    public function findByFilters(
        ?string $search = null,
        ?MuscleGroup $primaryMuscle = null,
        ?EquipmentType $equipment = null,
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.primaryMuscles', 'pm')
            ->addSelect('pm');

        if ($search !== null && $search !== '') {
            $qb->andWhere('LOWER(e.name) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($primaryMuscle !== null) {
            $qb->andWhere(':muscle MEMBER OF e.primaryMuscles')
               ->setParameter('muscle', $primaryMuscle);
        }

        if ($equipment !== null) {
            $qb->andWhere('e.equipment = :equipment')
               ->setParameter('equipment', $equipment);
        }

        return $qb->orderBy('e.name', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Carga un ejercicio con todos sus músculos en una sola query (evita N+1).
     */
    public function findOneWithMuscles(string $id): ?Exercise
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.primaryMuscles', 'pm')
            ->leftJoin('e.secondaryMuscles', 'sm')
            ->addSelect('pm', 'sm')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
