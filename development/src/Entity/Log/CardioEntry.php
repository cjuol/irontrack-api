<?php

declare(strict_types=1);

namespace App\Entity\Log;

use App\Entity\Program\WeeklyCardioPlan;
use App\Enum\CardioType;
use App\Repository\Log\CardioEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CardioEntryRepository::class)]
#[ORM\Table(name: 'cardio_entries')]
class CardioEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: WorkoutSession::class, inversedBy: 'cardioEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WorkoutSession $workoutSession;

    /**
     * Plan de cardio semanal de referencia.
     * Null si el cardio no corresponde a un plan programado.
     */
    #[ORM\ManyToOne(targetEntity: WeeklyCardioPlan::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?WeeklyCardioPlan $weeklyCardioPlan = null;

    #[ORM\Column(type: 'string', enumType: CardioType::class)]
    private CardioType $cardioType;

    #[ORM\Column(type: 'integer')]
    private int $durationSeconds;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?string $distanceMeters = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $avgSpeedKmh = null;

    /** Inclinación en tanto por ciento (para cinta de correr). */
    #[ORM\Column(type: 'decimal', precision: 4, scale: 1, nullable: true)]
    private ?string $inclinePct = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    // --- Campos reservados para integraciones futuras ---

    /** @future Garmin / Polar */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $avgHeartRate = null;

    /** @future Fitbit / Garmin */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $caloriesBurned = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getWorkoutSession(): WorkoutSession
    {
        return $this->workoutSession;
    }

    public function setWorkoutSession(WorkoutSession $workoutSession): static
    {
        $this->workoutSession = $workoutSession;
        return $this;
    }

    public function getWeeklyCardioPlan(): ?WeeklyCardioPlan
    {
        return $this->weeklyCardioPlan;
    }

    public function setWeeklyCardioPlan(?WeeklyCardioPlan $weeklyCardioPlan): static
    {
        $this->weeklyCardioPlan = $weeklyCardioPlan;
        return $this;
    }

    public function getCardioType(): CardioType
    {
        return $this->cardioType;
    }

    public function setCardioType(CardioType $cardioType): static
    {
        $this->cardioType = $cardioType;
        return $this;
    }

    public function getDurationSeconds(): int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(int $durationSeconds): static
    {
        $this->durationSeconds = $durationSeconds;
        return $this;
    }

    public function getDistanceMeters(): ?float
    {
        return $this->distanceMeters !== null ? (float) $this->distanceMeters : null;
    }

    public function setDistanceMeters(?float $distanceMeters): static
    {
        $this->distanceMeters = $distanceMeters !== null ? (string) $distanceMeters : null;
        return $this;
    }

    public function getAvgSpeedKmh(): ?float
    {
        return $this->avgSpeedKmh !== null ? (float) $this->avgSpeedKmh : null;
    }

    public function setAvgSpeedKmh(?float $avgSpeedKmh): static
    {
        $this->avgSpeedKmh = $avgSpeedKmh !== null ? (string) $avgSpeedKmh : null;
        return $this;
    }

    public function getInclinePct(): ?float
    {
        return $this->inclinePct !== null ? (float) $this->inclinePct : null;
    }

    public function setInclinePct(?float $inclinePct): static
    {
        $this->inclinePct = $inclinePct !== null ? (string) $inclinePct : null;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
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

    public function getCaloriesBurned(): ?int
    {
        return $this->caloriesBurned;
    }

    public function setCaloriesBurned(?int $caloriesBurned): static
    {
        $this->caloriesBurned = $caloriesBurned;
        return $this;
    }

    /** Pace en min/km. Null si no hay datos suficientes. */
    public function getPaceMinPerKm(): ?float
    {
        $distance = $this->getDistanceMeters();
        if ($distance === null || $distance <= 0) {
            return null;
        }
        return round(($this->durationSeconds / 60) / ($distance / 1000), 2);
    }
}