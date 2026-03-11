<?php

declare(strict_types=1);

namespace App\Entity\Log;

use App\Entity\User;
use App\Enum\TrainingDayType;
use App\Repository\Log\TrainingDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TrainingDayRepository::class)]
#[ORM\Table(name: 'training_days')]
#[ORM\UniqueConstraint(name: 'uq_training_day_user_date', columns: ['user_id', 'date'])]
#[ORM\Index(name: 'idx_training_day_date', columns: ['date'])]
class TrainingDay
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'trainingDays')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'string', enumType: TrainingDayType::class)]
    private TrainingDayType $type;

    /**
     * Objetivo de pasos del día.
     * Se inicializa desde el Mesocycle activo vía TrainingDayService,
     * usando stepGoalTrainingDay o stepGoalRestDay según el tipo de día.
     */
    #[ORM\Column(type: 'integer')]
    private int $stepGoal;

    /** Pasos reales registrados. Null hasta que el usuario los registra. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stepsActual = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /** Reservado para integración con Garmin Connect. */
    #[ORM\Column(type: 'decimal', precision: 4, scale: 2, nullable: true)]
    private ?string $sleepHours = null;

    /** Reservado para integración con Garmin / Fitbit. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $restingHeartRate = null;

    /** Reservado para integración con Fitbit. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalCaloriesDay = null;

    /** @var Collection<int, WorkoutSession> */
    #[ORM\OneToMany(mappedBy: 'trainingDay', targetEntity: WorkoutSession::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['startedAt' => 'ASC'])]
    private Collection $workoutSessions;

    public function __construct()
    {
        $this->id              = Uuid::v4();
        $this->workoutSessions = new ArrayCollection();
    }

    #[Groups(['training-day:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    #[Groups(['training-day:read'])]
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    #[Groups(['training-day:read'])]
    public function getType(): TrainingDayType
    {
        return $this->type;
    }

    public function setType(TrainingDayType $type): static
    {
        $this->type = $type;
        return $this;
    }

    #[Groups(['training-day:read'])]
    public function getStepGoal(): int
    {
        return $this->stepGoal;
    }

    public function setStepGoal(int $stepGoal): static
    {
        $this->stepGoal = $stepGoal;
        return $this;
    }

    #[Groups(['training-day:read'])]
    public function getStepsActual(): ?int
    {
        return $this->stepsActual;
    }

    public function setStepsActual(?int $stepsActual): static
    {
        $this->stepsActual = $stepsActual;
        return $this;
    }

    public function hasReachedStepGoal(): bool
    {
        return $this->stepsActual !== null && $this->stepsActual >= $this->stepGoal;
    }

    #[Groups(['training-day:read'])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getSleepHours(): ?float
    {
        return $this->sleepHours !== null ? (float) $this->sleepHours : null;
    }

    public function setSleepHours(?float $sleepHours): static
    {
        $this->sleepHours = $sleepHours !== null ? (string) $sleepHours : null;
        return $this;
    }

    public function getRestingHeartRate(): ?int
    {
        return $this->restingHeartRate;
    }

    public function setRestingHeartRate(?int $restingHeartRate): static
    {
        $this->restingHeartRate = $restingHeartRate;
        return $this;
    }

    public function getTotalCaloriesDay(): ?int
    {
        return $this->totalCaloriesDay;
    }

    public function setTotalCaloriesDay(?int $totalCaloriesDay): static
    {
        $this->totalCaloriesDay = $totalCaloriesDay;
        return $this;
    }

    /** @return Collection<int, WorkoutSession> */
    #[Groups(['training-day:read'])]
    public function getWorkoutSessions(): Collection
    {
        return $this->workoutSessions;
    }

    public function addWorkoutSession(WorkoutSession $session): static
    {
        if (!$this->workoutSessions->contains($session)) {
            $this->workoutSessions->add($session);
            $session->setTrainingDay($this);
        }
        return $this;
    }

    public function isTrainingDay(): bool
    {
        return $this->type === TrainingDayType::TRAINING;
    }
}