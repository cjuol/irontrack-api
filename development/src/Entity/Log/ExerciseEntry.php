<?php

declare(strict_types=1);

namespace App\Entity\Log;

use App\Entity\Catalog\Exercise;
use App\Entity\Program\PlannedExercise;
use App\Repository\Log\ExerciseEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExerciseEntryRepository::class)]
#[ORM\Table(name: 'exercise_entries')]
#[ORM\Index(name: 'idx_exercise_entry_workout_session', columns: ['workout_session_id', 'sort_order'])]
class ExerciseEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: WorkoutSession::class, inversedBy: 'exerciseEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WorkoutSession $workoutSession;

    /**
     * Ejercicio denormalizado para poder hacer queries de historial sin JOINs adicionales.
     * Duplica la referencia que ya tiene PlannedExercise, pero vale la pena.
     */
    #[ORM\ManyToOne(targetEntity: Exercise::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Exercise $exercise;

    /** Null para ejercicios añadidos libremente fuera de la plantilla. */
    #[ORM\ManyToOne(targetEntity: PlannedExercise::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?PlannedExercise $plannedExercise = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /** @var Collection<int, SetEntry> */
    #[ORM\OneToMany(mappedBy: 'exerciseEntry', targetEntity: SetEntry::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $setEntries;

    /**
     * Rendimiento anterior del usuario en este ejercicio.
     * Campo transitorio — no persiste en BD. Lo rellena PreviousPerformanceFetcher.
     */
    private ?array $previousPerformance = null;

    public function __construct()
    {
        $this->id         = Uuid::v4();
        $this->setEntries = new ArrayCollection();
    }

    #[Groups(['session:read'])]
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

    #[Groups(['session:read'])]
    public function getExercise(): Exercise
    {
        return $this->exercise;
    }

    public function setExercise(Exercise $exercise): static
    {
        $this->exercise = $exercise;
        return $this;
    }

    public function getPlannedExercise(): ?PlannedExercise
    {
        return $this->plannedExercise;
    }

    public function setPlannedExercise(?PlannedExercise $plannedExercise): static
    {
        $this->plannedExercise = $plannedExercise;
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

    /** @return Collection<int, SetEntry> */
    #[Groups(['session:read'])]
    public function getSetEntries(): Collection
    {
        return $this->setEntries;
    }

    public function addSetEntry(SetEntry $entry): static
    {
        if (!$this->setEntries->contains($entry)) {
            $this->setEntries->add($entry);
            $entry->setExerciseEntry($this);
        }
        return $this;
    }

    #[Groups(['session:read'])]
    public function getPreviousPerformance(): ?array
    {
        return $this->previousPerformance;
    }

    public function setPreviousPerformance(?array $previousPerformance): static
    {
        $this->previousPerformance = $previousPerformance;
        return $this;
    }
}
