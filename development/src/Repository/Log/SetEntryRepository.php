<?php

declare(strict_types=1);

namespace App\Repository\Log;

use App\Entity\Catalog\Exercise;
use App\Entity\Log\SetEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SetEntry>
 *
 * Repositorio central para todo lo relacionado con historial de rendimiento,
 * progresión de carga y cálculo de métricas. Es el más utilizado por los
 * servicios PreviousPerformanceFetcher y ProgressionAnalyzer (Fase 4).
 */
class SetEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SetEntry::class);
    }

    // -------------------------------------------------------------------------
    // Historial de rendimiento (usado por PreviousPerformanceFetcher)
    // -------------------------------------------------------------------------

    /**
     * Devuelve las series de la última sesión en que el usuario realizó un ejercicio.
     *
     * La lógica es: encontrar el TrainingDay más reciente que contenga ese ejercicio
     * y devolver todas las series de esa sesión, ordenadas por sortOrder.
     *
     * Usado por SessionPreloader para precargar los pesos de referencia.
     *
     * @return SetEntry[]
     */
    public function findLastPerformance(Exercise $exercise, User $user): array
    {
        // Primero encontramos la fecha de la última sesión con ese ejercicio
        $lastDate = $this->createQueryBuilder('se')
            ->select('MAX(td.date)')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        if ($lastDate === null) {
            return [];
        }

        // Luego traemos todas las series de esa fecha
        return $this->createQueryBuilder('se')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->andWhere('td.date = :lastDate')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->setParameter('lastDate', $lastDate)
            ->orderBy('se.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las últimas N sesiones en que el usuario realizó un ejercicio,
     * con todas sus series. Formato agrupado por sesión.
     *
     * Usado por GET /api/v1/exercises/{id}/history
     *
     * Resultado: [
     *   ['date' => '2026-02-10', 'sets' => [SetEntry, ...]],
     *   ['date' => '2026-01-27', 'sets' => [SetEntry, ...]],
     * ]
     *
     * @return array<int, array{date: string, sessionId: string, sets: SetEntry[]}>
     */
    public function findPerformanceHistory(
        Exercise $exercise,
        User $user,
        int $limit = 10,
    ): array {
        // Buscamos las fechas de las últimas N sesiones con ese ejercicio
        $dates = $this->createQueryBuilder('se')
            ->select('DISTINCT td.date AS date, IDENTITY(ws.id) AS sessionId')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->orderBy('td.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if (empty($dates)) {
            return [];
        }

        $dateValues = array_column($dates, 'date');

        // Cargamos todas las series de esas fechas en una sola query
        $sets = $this->createQueryBuilder('se')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->andWhere('td.date IN (:dates)')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->setParameter('dates', $dateValues)
            ->orderBy('td.date', 'DESC')
            ->addOrderBy('se.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();

        // Agrupamos las series por fecha
        $grouped = [];
        foreach ($sets as $set) {
            /** @var SetEntry $set */
            $date = $set->getExerciseEntry()
                        ->getWorkoutSession()
                        ->getTrainingDay()
                        ->getDate()
                        ->format('Y-m-d');

            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'date'      => $date,
                    'sessionId' => (string) $set->getExerciseEntry()->getWorkoutSession()->getId(),
                    'sets'      => [],
                ];
            }
            $grouped[$date]['sets'][] = $set;
        }

        return array_values($grouped);
    }

    // -------------------------------------------------------------------------
    // Métricas de progresión (Fase 4 — usadas por ProgressionAnalyzer)
    // -------------------------------------------------------------------------

    /**
     * Devuelve el peso máximo registrado por sesión para un ejercicio,
     * en los últimos N días.
     *
     * Devuelve datos brutos para que ProgressionAnalyzer aplique
     * estadística robusta (StatGuard) sobre ellos.
     *
     * Resultado: [['date' => '2026-01-10', 'maxWeight' => 100.0], ...]
     *
     * @return array<int, array{date: string, maxWeight: float}>
     */
    public function findMaxWeightPerSession(
        Exercise $exercise,
        User $user,
        int $days = 90,
    ): array {
        $since = (new \DateTimeImmutable())->modify("-{$days} days");

        return $this->createQueryBuilder('se')
            ->select('td.date AS date, MAX(se.weightKg) AS maxWeight')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->andWhere('td.date >= :since')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->setParameter('since', $since->format('Y-m-d'))
            ->groupBy('td.date')
            ->orderBy('td.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve el 1RM estimado (Epley) más alto por sesión.
     *
     * La fórmula Epley se aplica en PHP (no en SQL) para mantener
     * la lógica centralizada en SetEntry::getEstimated1RM().
     *
     * @return array<int, array{date: string, estimated1rm: float}>
     */
    public function findEstimated1RMHistory(
        Exercise $exercise,
        User $user,
        int $days = 90,
    ): array {
        $since = (new \DateTimeImmutable())->modify("-{$days} days");

        /** @var SetEntry[] $sets */
        $sets = $this->createQueryBuilder('se')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->andWhere('td.date >= :since')
            ->andWhere('se.repsCompleted > 0')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->setParameter('since', $since->format('Y-m-d'))
            ->orderBy('td.date', 'ASC')
            ->getQuery()
            ->getResult();

        // Agrupamos por fecha y nos quedamos con el 1RM más alto de cada sesión
        $byDate = [];
        foreach ($sets as $set) {
            $date    = $set->getExerciseEntry()->getWorkoutSession()->getTrainingDay()->getDate()->format('Y-m-d');
            $orm     = $set->getEstimated1RM();
            if ($orm === null) {
                continue;
            }
            if (!isset($byDate[$date]) || $orm > $byDate[$date]) {
                $byDate[$date] = $orm;
            }
        }

        return array_map(
            fn(string $date, float $orm) => ['date' => $date, 'estimated1rm' => $orm],
            array_keys($byDate),
            array_values($byDate),
        );
    }

    // -------------------------------------------------------------------------
    // Volumen por grupo muscular (Fase 4 — dashboard)
    // -------------------------------------------------------------------------

    /**
     * Devuelve el volumen total (kg × reps) agrupado por grupo muscular primario
     * en un rango de fechas.
     *
     * Usado por el endpoint de volumen semanal del dashboard.
     *
     * Resultado: [['muscle' => 'Pecho', 'totalVolume' => 12450.0], ...]
     *
     * @return array<int, array{muscle: string, totalVolume: float}>
     */
    public function findVolumeByMuscleGroup(
        User $user,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        return $this->createQueryBuilder('se')
            ->select('mg.name AS muscle, SUM(se.weightKg * se.repsCompleted) AS totalVolume')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.exercise', 'ex')
            ->join('ex.primaryMuscles', 'mg')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('td.user = :user')
            ->andWhere('td.date >= :from')
            ->andWhere('td.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->groupBy('mg.id', 'mg.name')
            ->orderBy('totalVolume', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve el número de series totales por grupo muscular en un rango.
     * Complementa findVolumeByMuscleGroup para calcular la distribución de frecuencia.
     *
     * @return array<int, array{muscle: string, totalSets: int}>
     */
    public function findSetCountByMuscleGroup(
        User $user,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        return $this->createQueryBuilder('se')
            ->select('mg.name AS muscle, COUNT(se.id) AS totalSets')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.exercise', 'ex')
            ->join('ex.primaryMuscles', 'mg')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('td.user = :user')
            ->andWhere('td.date >= :from')
            ->andWhere('td.date <= :to')
            ->setParameter('user', $user)
            ->setParameter('from', $from->format('Y-m-d'))
            ->setParameter('to', $to->format('Y-m-d'))
            ->groupBy('mg.id', 'mg.name')
            ->orderBy('totalSets', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // -------------------------------------------------------------------------
    // PRs (Personal Records) — Fase 4
    // -------------------------------------------------------------------------

    /**
     * Devuelve el PR (peso máximo levantado) del usuario en cada ejercicio.
     *
     * Resultado: [['exerciseName' => 'Sentadilla', 'maxWeight' => 120.0, 'date' => '2026-01-15'], ...]
     *
     * @return array<int, array{exerciseName: string, maxWeight: float, date: string}>
     */
    public function findPersonalRecords(User $user): array
    {
        return $this->createQueryBuilder('se')
            ->select(
                'ex.name AS exerciseName',
                'MAX(se.weightKg) AS maxWeight',
                'MAX(td.date) AS date',
            )
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.exercise', 'ex')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('td.user = :user')
            ->setParameter('user', $user)
            ->groupBy('ex.id', 'ex.name')
            ->orderBy('ex.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Comprueba si una serie concreta es un PR para ese ejercicio y usuario.
     * Útil para mostrar el badge "🏆 PR" en tiempo real al registrar la serie.
     */
    public function isPersonalRecord(SetEntry $setEntry, User $user): bool
    {
        $exercise = $setEntry->getExerciseEntry()->getExercise();

        $maxWeight = $this->createQueryBuilder('se')
            ->select('MAX(se.weightKg)')
            ->join('se.exerciseEntry', 'ee')
            ->join('ee.workoutSession', 'ws')
            ->join('ws.trainingDay', 'td')
            ->where('ee.exercise = :exercise')
            ->andWhere('td.user = :user')
            ->andWhere('se.id != :currentId')
            ->setParameter('exercise', $exercise)
            ->setParameter('user', $user)
            ->setParameter('currentId', $setEntry->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if ($maxWeight === null) {
            return true; // Primera vez que hace el ejercicio, siempre es PR
        }

        return $setEntry->getWeightKg() > (float) $maxWeight;
    }
}
