<?php

declare(strict_types=1);

namespace App\Repository\Log;

use App\Entity\Log\TrainingDay;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainingDay>
 */
class TrainingDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingDay::class);
    }

    /**
     * Busca el día de entrenamiento de un usuario en una fecha concreta.
     * Hay un índice UNIQUE en (user_id, date), así que esta query es O(1).
     */
    public function findByUserAndDate(User $user, \DateTimeImmutable $date): ?TrainingDay
    {
        return $this->createQueryBuilder('td')
            ->where('td.user = :user')
            ->andWhere('td.date = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve los días de entrenamiento de un usuario en un mes concreto.
     * Usado por el endpoint de calendario del dashboard.
     *
     * @return TrainingDay[]
     */
    public function findByUserAndMonth(User $user, int $year, int $month): array
    {
        $start = new \DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $end   = $start->modify('last day of this month');

        return $this->createQueryBuilder('td')
            ->where('td.user = :user')
            ->andWhere('td.date >= :start')
            ->andWhere('td.date <= :end')
            ->setParameter('user', $user)
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'))
            ->orderBy('td.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Carga un día con sus sesiones de entrenamiento (evita lazy loading en el controller).
     */
    public function findOneWithSessions(User $user, \DateTimeImmutable $date): ?TrainingDay
    {
        return $this->createQueryBuilder('td')
            ->leftJoin('td.workoutSessions', 'ws')
            ->leftJoin('ws.sessionTemplate', 'st')
            ->addSelect('ws', 'st')
            ->where('td.user = :user')
            ->andWhere('td.date = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve los N últimos días de entrenamiento del usuario.
     * Útil para calcular racha de días activos o resumen semanal.
     *
     * @return TrainingDay[]
     */
    public function findLastDays(User $user, int $limit = 7): array
    {
        return $this->createQueryBuilder('td')
            ->where('td.user = :user')
            ->setParameter('user', $user)
            ->orderBy('td.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve los días de entrenamiento entre dos fechas.
     * Usado por los endpoints de métricas y comparativa entre mesociclos.
     *
     * @return TrainingDay[]
     */
    public function findByUserAndDateRange(
        User $user,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        return $this->createQueryBuilder('td')
            ->where('td.user = :user')
            ->andWhere('td.date >= :from')
            ->andWhere('td.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->orderBy('td.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Cuenta los días de entrenamiento completados (con sesión cerrada) en un rango.
     * Usado para el resumen semanal/mensual del dashboard.
     */
    public function countTrainingDaysInRange(
        User $user,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): int {
        return (int) $this->createQueryBuilder('td')
            ->select('COUNT(td.id)')
            ->join('td.workoutSessions', 'ws')
            ->where('td.user = :user')
            ->andWhere('td.date >= :from')
            ->andWhere('td.date <= :to')
            ->andWhere('ws.finishedAt IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Suma de pasos reales en un rango de fechas.
     * Null si no hay ningún día con pasos registrados.
     */
    public function sumStepsInRange(
        User $user,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): ?int {
        $result = $this->createQueryBuilder('td')
            ->select('SUM(td.stepsActual)')
            ->where('td.user = :user')
            ->andWhere('td.date >= :from')
            ->andWhere('td.date <= :to')
            ->andWhere('td.stepsActual IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) $result : null;
    }
}
