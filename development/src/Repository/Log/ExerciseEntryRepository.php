<?php

declare(strict_types=1);

namespace App\Repository\Log;

use App\Entity\Catalog\Exercise;
use App\Entity\Log\ExerciseEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExerciseEntry>
 */
class ExerciseEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExerciseEntry::class);
    }

    /**
     * Devuelve las entradas de ejercicio de una sesión con sus series ya cargadas.
     * Evita N+1 al renderizar la sesión en el frontend.
     *
     * @return ExerciseEntry[]
     */
    public function findBySessionWithSets(string $sessionId): array
    {
        return $this->createQueryBuilder('ee')
            ->leftJoin('ee.setEntries', 'se')
            ->leftJoin('ee.exercise', 'ex')
            ->addSelect('se', 'ex')
            ->join('ee.workoutSession', 'ws')
            ->where('ws.id = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('ee.sortOrder', 'ASC')
            ->addOrderBy('se.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve el número de veces que el usuario ha ejecutado un ejercicio concreto.
     * Útil para mostrar en el catálogo de ejercicios.
     */
    public function countByUserAndExercise(User $user, Exercise $exercise): int
    {
        return (int) $this->createQueryBuilder('ee')
            ->select('COUNT(ee.id)')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('td.user = :user')
            ->andWhere('ee.exercise = :exercise')
            ->setParameter('user', $user)
            ->setParameter('exercise', $exercise)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
