<?php

declare(strict_types=1);

namespace App\Service\Log;

use App\Entity\Log\WorkoutSession;
use App\Entity\User;
use App\Repository\Log\SetEntryRepository;
use App\Repository\Log\TrainingDayRepository;
use App\Repository\Log\WorkoutSessionRepository;
use App\Repository\Program\MesocycleRepository;

class DashboardAggregator
{
    public function __construct(
        private readonly MesocycleRepository      $mesocycleRepository,
        private readonly TrainingDayRepository    $trainingDayRepository,
        private readonly WorkoutSessionRepository $sessionRepository,
        private readonly SetEntryRepository       $setEntryRepository,
    ) {}

    /**
     * Ensambla el resumen del mesociclo activo para el dashboard.
     *
     * Si no hay mesociclo activo, devuelve los datos generales de las últimas 4 semanas.
     */
    public function getSummary(User $user): array
    {
        $today     = new \DateTimeImmutable('today');
        $mesocycle = $this->mesocycleRepository->findActiveForUser($user, $today);

        $from = $mesocycle?->getStartDate() ?? $today->modify('-28 days');
        $to   = $mesocycle?->getEndDate()   ?? $today;

        $recentSessions        = $this->sessionRepository->findLastCompleted($user, 5);
        $avgDuration           = $this->sessionRepository->getAvgDurationInRange($user, $today->modify('-28 days'), $today);
        $trainingDaysCompleted = $this->trainingDayRepository->countTrainingDaysInRange($user, $from, $to);
        $totalSteps            = $this->trainingDayRepository->sumStepsInRange($user, $from, $to);
        $personalRecords       = $this->setEntryRepository->findPersonalRecords($user);

        return [
            'mesocycle'              => $mesocycle === null ? null : [
                'id'          => (string) $mesocycle->getId(),
                'name'        => $mesocycle->getName(),
                'objective'   => $mesocycle->getObjective(),
                'startDate'   => $mesocycle->getStartDate()->format('Y-m-d'),
                'endDate'     => $mesocycle->getEndDate()->format('Y-m-d'),
                'numWeeks'    => $mesocycle->getNumWeeks(),
                'currentWeek' => $mesocycle->getWeekNumber($today),
            ],
            'trainingDaysCompleted'  => $trainingDaysCompleted,
            'totalSteps'             => $totalSteps ?? 0,
            'avgSessionDurationMins' => $avgDuration !== null ? round($avgDuration / 60, 1) : null,
            'totalPersonalRecords'   => count($personalRecords),
            'recentSessions'         => array_map(
                static fn(WorkoutSession $ws) => [
                    'id'              => (string) $ws->getId(),
                    'date'            => $ws->getTrainingDay()->getDate()->format('Y-m-d'),
                    'startedAt'       => $ws->getStartedAt()->format(\DateTimeInterface::ATOM),
                    'durationMins'    => $ws->getDurationSeconds() !== null
                        ? round($ws->getDurationSeconds() / 60, 1)
                        : null,
                    'perceivedEffort' => $ws->getPerceivedEffort(),
                ],
                $recentSessions,
            ),
        ];
    }
}
