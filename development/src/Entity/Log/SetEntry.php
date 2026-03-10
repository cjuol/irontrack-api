<?php

declare(strict_types=1);

namespace App\Entity\Log;

use App\Entity\Program\PlannedSet;
use App\Repository\Log\SetEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SetEntryRepository::class)]
#[ORM\Table(name: 'set_entries')]
#[ORM\Index(name: 'idx_set_entry_exercise_entry', columns: ['exercise_entry_id', 'sort_order'])]
class SetEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ExerciseEntry::class, inversedBy: 'setEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ExerciseEntry $exerciseEntry;

    /**
     * Serie planificada de referencia.
     * Null para series añadidas fuera de la plantilla.
     */
    #[ORM\ManyToOne(targetEntity: PlannedSet::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PlannedSet $plannedSet = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private string $weightKg;

    #[ORM\Column(type: 'integer')]
    private int $repsCompleted;

    /**
     * Reps In Reserve reales tras la serie.
     * Null si la serie fue hasta el fallo (toFailure = true).
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rirActual = null;

    /** Indica si la serie se llevó hasta el fallo muscular. */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $toFailure = false;

    /** Para ejercicios isométricos o de tiempo (plancha, farmer carry...). */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $durationSeconds = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    #[Groups(['session:read', 'performance:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getExerciseEntry(): ExerciseEntry
    {
        return $this->exerciseEntry;
    }

    public function setExerciseEntry(ExerciseEntry $exerciseEntry): static
    {
        $this->exerciseEntry = $exerciseEntry;
        return $this;
    }

    public function getPlannedSet(): ?PlannedSet
    {
        return $this->plannedSet;
    }

    public function setPlannedSet(?PlannedSet $plannedSet): static
    {
        $this->plannedSet = $plannedSet;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    #[Groups(['session:read', 'performance:read'])]
    public function getWeightKg(): float
    {
        return (float) $this->weightKg;
    }

    public function setWeightKg(float $weightKg): static
    {
        $this->weightKg = (string) $weightKg;
        return $this;
    }

    #[Groups(['session:read', 'performance:read'])]
    public function getRepsCompleted(): int
    {
        return $this->repsCompleted;
    }

    public function setRepsCompleted(int $repsCompleted): static
    {
        $this->repsCompleted = $repsCompleted;
        return $this;
    }

    #[Groups(['session:read', 'performance:read'])]
    public function getRirActual(): ?int
    {
        return $this->rirActual;
    }

    public function setRirActual(?int $rirActual): static
    {
        $this->rirActual = $rirActual;
        return $this;
    }

    #[Groups(['session:read', 'performance:read'])]
    public function isToFailure(): bool
    {
        return $this->toFailure;
    }

    public function setToFailure(bool $toFailure): static
    {
        $this->toFailure = $toFailure;
        if ($toFailure) {
            $this->rirActual = null;
        }
        return $this;
    }

    #[Groups(['session:read'])]
    public function getDurationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(?int $durationSeconds): static
    {
        $this->durationSeconds = $durationSeconds;
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

    /** Volumen de la serie: kg × reps. */
    #[Groups(['session:read', 'performance:read'])]
    public function getVolume(): float
    {
        return $this->getWeightKg() * $this->repsCompleted;
    }

    /**
     * 1RM estimado usando la fórmula de Epley.
     * Sólo válido para series de más de 1 rep.
     */
    #[Groups(['session:read', 'performance:read'])]
    public function getEstimated1RM(): ?float
    {
        if ($this->repsCompleted <= 1) {
            return $this->getWeightKg();
        }
        return round($this->getWeightKg() * (1 + $this->repsCompleted / 30), 2);
    }
}