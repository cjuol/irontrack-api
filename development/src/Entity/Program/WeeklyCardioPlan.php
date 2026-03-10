<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Enum\CardioFormat;
use App\Repository\Program\WeeklyCardioPlanRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WeeklyCardioPlanRepository::class)]
#[ORM\Table(name: 'weekly_cardio_plans')]
#[ORM\UniqueConstraint(name: 'uq_cardio_block_week', columns: ['exercise_block_id', 'week_number'])]
class WeeklyCardioPlan
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ExerciseBlock::class, inversedBy: 'weeklyCardioPlans')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ExerciseBlock $exerciseBlock;

    #[ORM\Column(type: 'integer')]
    private int $weekNumber;

    #[ORM\Column(type: 'string', enumType: CardioFormat::class)]
    private CardioFormat $formatType;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationMinutes = null;

    /** Descripción completa del protocolo de cardio. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Intervalos del protocolo de cardio.
     * Formato: [
     *   {"work_duration": 60, "work_speed": 10.5, "rest_duration": 60, "rest_speed": 5.5, "rounds": 8}
     * ]
     * Null o vacío si es cardio continuo sin intervalos definidos.
     *
     * @var array<int, array{work_duration: int, work_speed: float, rest_duration: int, rest_speed: float, rounds: int}>
     */
    #[ORM\Column(type: 'json')]
    private array $intervals = [];

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getExerciseBlock(): ExerciseBlock
    {
        return $this->exerciseBlock;
    }

    public function setExerciseBlock(ExerciseBlock $exerciseBlock): static
    {
        $this->exerciseBlock = $exerciseBlock;
        return $this;
    }

    public function getWeekNumber(): int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): static
    {
        $this->weekNumber = $weekNumber;
        return $this;
    }

    public function getFormatType(): CardioFormat
    {
        return $this->formatType;
    }

    public function setFormatType(CardioFormat $formatType): static
    {
        $this->formatType = $formatType;
        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function setIntervals(array $intervals): static
    {
        $this->intervals = $intervals;
        return $this;
    }

    public function hasIntervals(): bool
    {
        return !empty($this->intervals);
    }
}