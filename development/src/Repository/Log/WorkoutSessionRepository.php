<?php

declare(strict_types=1);

namespace App\Repository\Log;

use App\Entity\Log\TrainingDay;
use App\Entity\Log\WorkoutSession;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkoutSession>
 */
class WorkoutSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkoutSession::class);
    }

    /**
     * Carga una sesión con todos sus entries para mostrar en el detalle de sesión.
     * Evita el problema N+1 al cargar ejercicios, series, cardio y metabólico
     * de golpe mediante múltiples LEFT JOIN.
     *
     * IMPORTANTE: Doctrine no puede hacer múltiples FETCH JOIN sobre colecciones
     * en la misma query sin paginación. En este caso usamos getResult() + hydration
     * y confiamos en que Doctrine deduplica los resultados correctamente.
     */
    public function findOneWithAllEntries(string $id): ?WorkoutSession
    {
        return $this->createQueryBuilder('ws')
            ->leftJoin('ws.exerciseEntries', 'ee')
            ->leftJoin('ee.setEntries', 'se')
            ->leftJoin('ee.exercise', 'ex')
            ->leftJoin('ee.plannedExercise', 'pe')
            ->leftJoin('ws.cardioEntries', 'ce')
            ->leftJoin('ws.metabolicEntries', 'me')
            ->addSelect('ee', 'se', 'ex', 'pe', 'ce', 'me')
            ->where('ws.id = :id')
            ->setParameter('id', $id)
            ->orderBy('ee.sortOrder', 'ASC')
            ->addOrderBy('se.sortOrder', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve las sesiones de un día de entrenamiento.
     *
     * @return WorkoutSession[]
     */
    public function findByTrainingDay(TrainingDay $trainingDay): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.trainingDay = :day')
            ->setParameter('day', $trainingDay)
            ->orderBy('ws.startedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las N últimas sesiones completadas del usuario.
     * Usado por el resumen del dashboard.
     *
     * @return WorkoutSession[]
     */
    public function findLastCompleted(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('ws')
            ->join('ws.trainingDay', 'td')
            ->where('td.user = :user')
            ->andWhere('ws.finishedAt IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('ws.startedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Duración media de sesiones completadas en un rango de fechas.
     * Devuelve la media en segundos, o null si no hay sesiones.
     *
     * Usa SQL nativo con EXTRACT(EPOCH FROM ...) en lugar de DQL porque
     * Doctrine no soporta de forma nativa la sustracción de timestamps en PostgreSQL.
     */
    public function getAvgDurationInRange(
        User $user,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): ?float {
        $result = $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT AVG(EXTRACT(EPOCH FROM (ws.finished_at - ws.started_at)))
               FROM workout_sessions ws
               JOIN training_days td ON td.id = ws.training_day_id
              WHERE td.user_id = :user
                AND td.date >= :from
                AND td.date <= :to
                AND ws.finished_at IS NOT NULL',
            [
                'user' => $user->getId()->toRfc4122(),
                'from' => $from->format('Y-m-d'),
                'to'   => $to->format('Y-m-d'),
            ],
        );

        return $result !== null ? (float) $result : null;
    }
}
