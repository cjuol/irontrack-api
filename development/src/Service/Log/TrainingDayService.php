<?php

declare(strict_types=1);

namespace App\Service\Log;

use App\Entity\Log\TrainingDay;
use App\Entity\User;
use App\Enum\TrainingDayType;
use App\Repository\Log\TrainingDayRepository;
use App\Repository\Program\MesocycleRepository;
use Doctrine\ORM\EntityManagerInterface;

class TrainingDayService
{
    public function __construct(
        private readonly TrainingDayRepository $trainingDayRepository,
        private readonly MesocycleRepository   $mesocycleRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    public function create(User $user, \DateTimeImmutable $date, TrainingDayType $type): TrainingDay
    {
        $day = (new TrainingDay())
            ->setUser($user)
            ->setDate($date)
            ->setType($type)
            ->setStepGoal($this->resolveStepGoal($user, $date, $type));

        $this->em->persist($day);
        $this->em->flush();

        return $day;
    }

    public function findOrCreate(User $user, \DateTimeImmutable $date, TrainingDayType $type): TrainingDay
    {
        return $this->trainingDayRepository->findByUserAndDate($user, $date)
            ?? $this->create($user, $date, $type);
    }

    public function changeType(TrainingDay $day, TrainingDayType $type): void
    {
        $day->setType($type)
            ->setStepGoal($this->resolveStepGoal($day->getUser(), $day->getDate(), $type));

        $this->em->flush();
    }

    public function logSteps(TrainingDay $day, int $steps): void
    {
        $day->setStepsActual($steps);
        $this->em->flush();
    }

    private function resolveStepGoal(User $user, \DateTimeImmutable $date, TrainingDayType $type): int
    {
        $mesocycle = $this->mesocycleRepository->findActiveForUser($user, $date);

        if ($mesocycle === null) {
            return 10000;
        }

        return $type === TrainingDayType::TRAINING
            ? $mesocycle->getStepGoalTrainingDay()
            : $mesocycle->getStepGoalRestDay();
    }
}
