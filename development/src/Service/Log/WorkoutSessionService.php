<?php

declare(strict_types=1);

namespace App\Service\Log;

use App\Entity\Catalog\Exercise;
use App\Entity\Log\CardioEntry;
use App\Entity\Log\ExerciseEntry;
use App\Entity\Log\MetabolicEntry;
use App\Entity\Log\SetEntry;
use App\Entity\Log\TrainingDay;
use App\Entity\Log\WorkoutSession;
use App\Entity\Program\PlannedExercise;
use App\Entity\Program\PlannedSet;
use App\Entity\Program\SessionTemplate;
use App\Entity\Program\WeeklyCardioPlan;
use App\Entity\Program\WeeklyMetabolicPlan;
use App\Enum\CardioType;
use Doctrine\ORM\EntityManagerInterface;

class WorkoutSessionService
{
    public function __construct(
        private readonly EntityManagerInterface     $em,
        private readonly PreviousPerformanceFetcher $previousPerformance,
    ) {}

    public function create(TrainingDay $day, ?SessionTemplate $template = null): WorkoutSession
    {
        $session = (new WorkoutSession())
            ->setTrainingDay($day)
            ->setSessionTemplate($template);

        $this->em->persist($session);
        $this->em->flush();

        return $session;
    }

    public function addPlannedExercise(WorkoutSession $session, PlannedExercise $planned, int $sortOrder): ExerciseEntry
    {
        $entry = (new ExerciseEntry())
            ->setWorkoutSession($session)
            ->setExercise($planned->getExercise())
            ->setPlannedExercise($planned)
            ->setSortOrder($sortOrder);

        $this->attachPreviousPerformance($entry);
        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    public function addFreeExercise(WorkoutSession $session, Exercise $exercise, int $sortOrder): ExerciseEntry
    {
        $entry = (new ExerciseEntry())
            ->setWorkoutSession($session)
            ->setExercise($exercise)
            ->setSortOrder($sortOrder);

        $this->attachPreviousPerformance($entry);
        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    public function logSet(
        ExerciseEntry $entry,
        float         $weightKg,
        int           $reps,
        ?int          $rir,
        bool          $toFailure,
        ?PlannedSet   $planned = null,
    ): SetEntry {
        $sortOrder = $entry->getSetEntries()->count() + 1;

        $set = (new SetEntry())
            ->setExerciseEntry($entry)
            ->setPlannedSet($planned)
            ->setSortOrder($sortOrder)
            ->setWeightKg($weightKg)
            ->setRepsCompleted($reps)
            ->setRirActual($rir)
            ->setToFailure($toFailure);

        $this->em->persist($set);
        $this->em->flush();

        return $set;
    }

    public function editSet(SetEntry $set, float $weightKg, int $reps, ?int $rir, bool $toFailure): void
    {
        $set->setWeightKg($weightKg)
            ->setRepsCompleted($reps)
            ->setRirActual($rir)
            ->setToFailure($toFailure);

        $this->em->flush();
    }

    public function deleteSet(SetEntry $set): void
    {
        $this->em->remove($set);
        $this->em->flush();
    }

    public function logCardio(
        WorkoutSession    $session,
        CardioType        $type,
        int               $durationSeconds,
        ?float            $distanceMeters = null,
        ?float            $avgSpeedKmh = null,
        ?float            $inclinePct = null,
        ?WeeklyCardioPlan $plan = null,
        ?string           $notes = null,
    ): CardioEntry {
        $entry = (new CardioEntry())
            ->setWorkoutSession($session)
            ->setCardioType($type)
            ->setDurationSeconds($durationSeconds)
            ->setDistanceMeters($distanceMeters)
            ->setAvgSpeedKmh($avgSpeedKmh)
            ->setInclinePct($inclinePct)
            ->setWeeklyCardioPlan($plan)
            ->setNotes($notes);

        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    public function editCardio(CardioEntry $entry, int $durationSeconds, ?float $distanceMeters, ?string $notes): void
    {
        $entry->setDurationSeconds($durationSeconds)
              ->setDistanceMeters($distanceMeters)
              ->setNotes($notes);

        $this->em->flush();
    }

    public function logMetabolic(
        WorkoutSession       $session,
        ?WeeklyMetabolicPlan $plan = null,
        ?int                 $weekNumber = null,
        ?int                 $rounds = null,
        ?int                 $timeSeconds = null,
        ?string              $result = null,
        ?string              $notes = null,
    ): MetabolicEntry {
        $entry = (new MetabolicEntry())
            ->setWorkoutSession($session)
            ->setWeeklyMetabolicPlan($plan)
            ->setWeekNumber($weekNumber)
            ->setRoundsCompleted($rounds)
            ->setTimeSeconds($timeSeconds)
            ->setResult($result)
            ->setNotes($notes);

        $this->em->persist($entry);
        $this->em->flush();

        return $entry;
    }

    public function finish(WorkoutSession $session): void
    {
        $session->finish();
        $this->em->flush();
    }

    private function attachPreviousPerformance(ExerciseEntry $entry): void
    {
        $user    = $entry->getWorkoutSession()->getTrainingDay()->getUser();
        $summary = $this->previousPerformance->getLastPerformanceSummary($entry->getExercise(), $user);
        $entry->setPreviousPerformance($summary);
    }
}
