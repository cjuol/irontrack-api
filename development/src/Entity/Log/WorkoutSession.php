<?php

declare(strict_types=1);

namespace App\Entity\Log;

use App\Entity\Program\SessionTemplate;
use App\Repository\Log\WorkoutSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WorkoutSessionRepository::class)]
#[ORM\Table(name: 'workout_sessions')]
#[ORM\Index(name: 'idx_workout_session_training_day', columns: ['training_day_id'])]
class WorkoutSession
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: TrainingDay::class, inversedBy: 'workoutSessions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private TrainingDay $trainingDay;

    /**
     * Plantilla del mesociclo de la que deriva esta sesión.
     * Null para sesiones libres (no planificadas).
     */
    #[ORM\ManyToOne(targetEntity: SessionTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?SessionTemplate $sessionTemplate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $startedAt;

    /** Null hasta que el usuario cierra la sesión. */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    /** RPE (Rate of Perceived Exertion): escala 1-10. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $perceivedEffort = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    // --- Campos reservados para integraciones futuras ---

    /** @future Garmin / Strava */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalCaloriesBurned = null;

    /** @future Garmin / Polar */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $avgHeartRate = null;

    /** @future Garmin / Polar */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxHeartRate = null;

    /** @var Collection<int, ExerciseEntry> */
    #[ORM\OneToMany(mappedBy: 'workoutSession', targetEntity: ExerciseEntry::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $exerciseEntries;

    /** @var Collection<int, CardioEntry> */
    #[ORM\OneToMany(mappedBy: 'workoutSession', targetEntity: CardioEntry::class, cascade: ['persist', 'remove'])]
    private Collection $cardioEntries;

    /** @var Collection<int, MetabolicEntry> */
    #[ORM\OneToMany(mappedBy: 'workoutSession', targetEntity: MetabolicEntry::class, cascade: ['persist', 'remove'])]
    private Collection $metabolicEntries;

    // Campos transitorios — los rellena SessionPreloader en memoria, no persisten en BD

    private ?array $currentMetabolicPlan = null;

    private ?array $currentCardioPlan = null;

    public function __construct()
    {
        $this->id               = Uuid::v4();
        $this->startedAt        = new \DateTimeImmutable();
        $this->exerciseEntries  = new ArrayCollection();
        $this->cardioEntries    = new ArrayCollection();
        $this->metabolicEntries = new ArrayCollection();
    }

    #[Groups(['training-day:read', 'session:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTrainingDay(): TrainingDay
    {
        return $this->trainingDay;
    }

    public function setTrainingDay(TrainingDay $trainingDay): static
    {
        $this->trainingDay = $trainingDay;
        return $this;
    }

    #[Groups(['training-day:read', 'session:read'])]
    public function getSessionTemplate(): ?SessionTemplate
    {
        return $this->sessionTemplate;
    }

    public function setSessionTemplate(?SessionTemplate $sessionTemplate): static
    {
        $this->sessionTemplate = $sessionTemplate;
        return $this;
    }

    #[Groups(['training-day:read', 'session:read'])]
    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    #[Groups(['training-day:read', 'session:read'])]
    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function finish(): static
    {
        $this->finishedAt = new \DateTimeImmutable();
        return $this;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    #[Groups(['training-day:read', 'session:read'])]
    public function isFinished(): bool
    {
        return $this->finishedAt !== null;
    }

    /** Duración en segundos. Null si la sesión no está cerrada. */
    #[Groups(['training-day:read', 'session:read'])]
    public function getDurationSeconds(): ?int
    {
        if ($this->finishedAt === null) {
            return null;
        }
        return $this->finishedAt->getTimestamp() - $this->startedAt->getTimestamp();
    }

    #[Groups(['session:read'])]
    public function getPerceivedEffort(): ?int
    {
        return $this->perceivedEffort;
    }

    public function setPerceivedEffort(?int $perceivedEffort): static
    {
        $this->perceivedEffort = $perceivedEffort;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getTotalCaloriesBurned(): ?int
    {
        return $this->totalCaloriesBurned;
    }

    public function setTotalCaloriesBurned(?int $totalCaloriesBurned): static
    {
        $this->totalCaloriesBurned = $totalCaloriesBurned;
        return $this;
    }

    public function getAvgHeartRate(): ?int
    {
        return $this->avgHeartRate;
    }

    public function setAvgHeartRate(?int $avgHeartRate): static
    {
        $this->avgHeartRate = $avgHeartRate;
        return $this;
    }

    public function getMaxHeartRate(): ?int
    {
        return $this->maxHeartRate;
    }

    public function setMaxHeartRate(?int $maxHeartRate): static
    {
        $this->maxHeartRate = $maxHeartRate;
        return $this;
    }

    /** @return Collection<int, ExerciseEntry> */
    #[Groups(['session:read'])]
    public function getExerciseEntries(): Collection
    {
        return $this->exerciseEntries;
    }

    public function addExerciseEntry(ExerciseEntry $entry): static
    {
        if (!$this->exerciseEntries->contains($entry)) {
            $this->exerciseEntries->add($entry);
            $entry->setWorkoutSession($this);
        }
        return $this;
    }

    /** @return Collection<int, CardioEntry> */
    #[Groups(['session:read'])]
    public function getCardioEntries(): Collection
    {
        return $this->cardioEntries;
    }

    public function addCardioEntry(CardioEntry $entry): static
    {
        if (!$this->cardioEntries->contains($entry)) {
            $this->cardioEntries->add($entry);
            $entry->setWorkoutSession($this);
        }
        return $this;
    }

    /** @return Collection<int, MetabolicEntry> */
    #[Groups(['session:read'])]
    public function getMetabolicEntries(): Collection
    {
        return $this->metabolicEntries;
    }

    public function addMetabolicEntry(MetabolicEntry $entry): static
    {
        if (!$this->metabolicEntries->contains($entry)) {
            $this->metabolicEntries->add($entry);
            $entry->setWorkoutSession($this);
        }
        return $this;
    }

    #[Groups(['session:read'])]
    public function getCurrentMetabolicPlan(): ?array
    {
        return $this->currentMetabolicPlan;
    }

    public function setCurrentMetabolicPlan(?array $plan): static
    {
        $this->currentMetabolicPlan = $plan;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getCurrentCardioPlan(): ?array
    {
        return $this->currentCardioPlan;
    }

    public function setCurrentCardioPlan(?array $plan): static
    {
        $this->currentCardioPlan = $plan;
        return $this;
    }
}