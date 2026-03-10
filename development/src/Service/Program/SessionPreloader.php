<?php

declare(strict_types=1);

namespace App\Service\Program;

use App\Entity\Log\TrainingDay;
use App\Entity\Log\WorkoutSession;
use App\Entity\Program\ExerciseBlock;
use App\Entity\Program\SessionTemplate;
use App\Enum\BlockType;
use App\Repository\Program\MesocycleRepository;
use App\Repository\Program\SessionTemplateRepository;
use App\Service\Log\WorkoutSessionService;

class SessionPreloader
{
    public function __construct(
        private readonly MesocycleRepository      $mesocycleRepository,
        private readonly SessionTemplateRepository $sessionTemplateRepository,
        private readonly WorkoutSessionService     $sessionService,
    ) {}

    /**
     * Construye una sesión a partir de la plantilla con el sortOrder indicado del mesociclo activo.
     * Usa una query directa por sortOrder en lugar de cargar todas las plantillas del mesociclo.
     *
     * @throws \RuntimeException si no hay mesociclo activo o no existe plantilla con ese sortOrder.
     */
    public function preloadFromTemplate(TrainingDay $day, int $sortOrder): WorkoutSession
    {
        $mesocycle = $this->mesocycleRepository->findActiveForUser($day->getUser(), $day->getDate());

        if ($mesocycle === null) {
            throw new \RuntimeException('No hay mesociclo activo para la fecha indicada.');
        }

        $weekNumber = $mesocycle->getWeekNumber($day->getDate()) ?? 1;

        $template = $this->sessionTemplateRepository->findOneByMesocycleAndSortOrder($mesocycle, $sortOrder);

        if ($template === null) {
            throw new \RuntimeException(
                sprintf('No existe plantilla con sortOrder %d en el mesociclo activo.', $sortOrder)
            );
        }

        $template = $this->sessionTemplateRepository->findOneWithFullPlan($template->getId()->toRfc4122());

        return $this->buildSession($day, $template, $weekNumber);
    }

    /**
     * Construye una sesión a partir de una plantilla concreta (el llamador la elige).
     */
    public function preloadFromSpecificTemplate(TrainingDay $day, SessionTemplate $template): WorkoutSession
    {
        $loaded     = $this->sessionTemplateRepository->findOneWithFullPlan($template->getId()->toRfc4122());
        $weekNumber = $loaded->getMesocycle()->getWeekNumber($day->getDate()) ?? 1;

        return $this->buildSession($day, $loaded, $weekNumber);
    }

    private function buildSession(TrainingDay $day, SessionTemplate $template, int $weekNumber): WorkoutSession
    {
        $session = $this->sessionService->create($day, $template);

        foreach ($template->getExerciseBlocks() as $block) {
            match ($block->getType()) {
                BlockType::STRENGTH  => $this->addStrengthEntries($session, $block),
                BlockType::METABOLIC => $this->attachMetabolicPlan($session, $block, $weekNumber),
                BlockType::CARDIO    => $this->attachCardioPlan($session, $block, $weekNumber),
            };
        }

        return $session;
    }

    private function addStrengthEntries(WorkoutSession $session, ExerciseBlock $block): void
    {
        $sortOrder = 1;
        foreach ($block->getPlannedExercises() as $planned) {
            $this->sessionService->addPlannedExercise($session, $planned, $sortOrder++);
        }
    }

    private function attachMetabolicPlan(WorkoutSession $session, ExerciseBlock $block, int $weekNumber): void
    {
        $plan = $block->getMetabolicPlanForWeek($weekNumber);

        if ($plan === null) {
            return;
        }

        $session->setCurrentMetabolicPlan([
            'format'            => $plan->getFormatType()->value,
            'durationMinutes'   => $plan->getDurationMinutes(),
            'totalRounds'       => $plan->getTotalRounds(),
            'restBetweenRounds' => $plan->getRestBetweenRoundsSeconds(),
            'description'       => $plan->getDescription(),
            'exercises'         => $plan->getExercises(),
        ]);
    }

    private function attachCardioPlan(WorkoutSession $session, ExerciseBlock $block, int $weekNumber): void
    {
        $plan = $block->getCardioPlanForWeek($weekNumber);

        if ($plan === null) {
            return;
        }

        $session->setCurrentCardioPlan([
            'format'          => $plan->getFormatType()->value,
            'durationMinutes' => $plan->getDurationMinutes(),
            'description'     => $plan->getDescription(),
            'intervals'       => $plan->getIntervals(),
        ]);
    }
}
