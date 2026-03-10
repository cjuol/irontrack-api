<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Enum\MetabolicFormat;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'weekly_metabolic_plans')]
#[ORM\UniqueConstraint(name: 'uq_metabolic_block_week', columns: ['exercise_block_id', 'week_number'])]
class WeeklyMetabolicPlan
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ExerciseBlock::class, inversedBy: 'weeklyMetabolicPlans')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ExerciseBlock $exerciseBlock;

    #[ORM\Column(type: 'integer')]
    private int $weekNumber;

    #[ORM\Column(type: 'string', enumType: MetabolicFormat::class)]
    private MetabolicFormat $formatType;

    /** Duración total del bloque metabólico en minutos. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationMinutes = null;

    /** Número de rondas (null si no aplica, ej: EMOM). */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalRounds = null;

    /** Descanso entre rondas en segundos. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $restBetweenRoundsSeconds = null;

    /** Descripción completa del protocolo tal como la escribe el entrenador. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Ejercicios del bloque metabólico.
     * Formato: [{"name": "Burpees", "reps": 10, "notes": "sin pausa"}, ...]
     * Permite circuitos multi-ejercicio con sus rondas propias.
     *
     * @var array<int, array{name: string, reps: int|string, notes?: string}>
     */
    #[ORM\Column(type: 'json')]
    private array $exercises = [];

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

    public function getFormatType(): MetabolicFormat
    {
        return $this->formatType;
    }

    public function setFormatType(MetabolicFormat $formatType): static
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

    public function getTotalRounds(): ?int
    {
        return $this->totalRounds;
    }

    public function setTotalRounds(?int $totalRounds): static
    {
        $this->totalRounds = $totalRounds;
        return $this;
    }

    public function getRestBetweenRoundsSeconds(): ?int
    {
        return $this->restBetweenRoundsSeconds;
    }

    public function setRestBetweenRoundsSeconds(?int $restBetweenRoundsSeconds): static
    {
        $this->restBetweenRoundsSeconds = $restBetweenRoundsSeconds;
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

    public function getExercises(): array
    {
        return $this->exercises;
    }

    public function setExercises(array $exercises): static
    {
        $this->exercises = $exercises;
        return $this;
    }
}