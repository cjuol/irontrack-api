<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Enum\SetType;
use App\Enum\WeightModifier;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'planned_sets')]
class PlannedSet
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: PlannedExercise::class, inversedBy: 'plannedSets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PlannedExercise $plannedExercise;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    #[ORM\Column(type: 'string', enumType: SetType::class)]
    private SetType $setType = SetType::NORMAL;

    /** Reps mínimas del rango prescrito. Null para sets de tiempo (isométricos). */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $repsMin = null;

    /** Reps máximas del rango prescrito. Null si es AMRAP o fallo directo. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $repsMax = null;

    /**
     * Reps In Reserve prescritas.
     * Null cuando rirToFailure = true.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rir = null;

    /** Si es true, la serie se ejecuta hasta el fallo muscular (F en el PDF). */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $rirToFailure = false;

    /** Descanso prescrito en segundos antes de la siguiente serie. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $restSeconds = null;

    /**
     * Indicación del entrenador sobre ajuste de carga respecto a la serie anterior.
     * Null = sin indicación específica.
     */
    #[ORM\Column(type: 'string', nullable: true, enumType: WeightModifier::class)]
    private ?WeightModifier $weightModifier = null;

    /** Notas adicionales del entrenador (ej: "asiento tumbado", "agarre prono"). */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPlannedExercise(): PlannedExercise
    {
        return $this->plannedExercise;
    }

    public function setPlannedExercise(PlannedExercise $plannedExercise): static
    {
        $this->plannedExercise = $plannedExercise;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getSetType(): SetType
    {
        return $this->setType;
    }

    public function setSetType(SetType $setType): static
    {
        $this->setType = $setType;
        return $this;
    }

    public function getRepsMin(): ?int
    {
        return $this->repsMin;
    }

    public function setRepsMin(?int $repsMin): static
    {
        $this->repsMin = $repsMin;
        return $this;
    }

    public function getRepsMax(): ?int
    {
        return $this->repsMax;
    }

    public function setRepsMax(?int $repsMax): static
    {
        $this->repsMax = $repsMax;
        return $this;
    }

    public function getRir(): ?int
    {
        return $this->rir;
    }

    public function setRir(?int $rir): static
    {
        $this->rir = $rir;
        return $this;
    }

    public function isRirToFailure(): bool
    {
        return $this->rirToFailure;
    }

    public function setRirToFailure(bool $rirToFailure): static
    {
        $this->rirToFailure = $rirToFailure;
        if ($rirToFailure) {
            $this->rir = null;
        }
        return $this;
    }

    public function getRestSeconds(): ?int
    {
        return $this->restSeconds;
    }

    public function setRestSeconds(?int $restSeconds): static
    {
        $this->restSeconds = $restSeconds;
        return $this;
    }

    public function getWeightModifier(): ?WeightModifier
    {
        return $this->weightModifier;
    }

    public function setWeightModifier(?WeightModifier $weightModifier): static
    {
        $this->weightModifier = $weightModifier;
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

    /** Devuelve el rango de reps como string legible: "8-10", "6", "AMRAP" */
    public function getRepsLabel(): string
    {
        if ($this->rirToFailure && $this->setType === SetType::AMRAP) {
            return 'AMRAP';
        }
        if ($this->repsMin !== null && $this->repsMax !== null && $this->repsMin !== $this->repsMax) {
            return "{$this->repsMin}-{$this->repsMax}";
        }
        if ($this->repsMin !== null) {
            return (string) $this->repsMin;
        }
        return '—';
    }
}