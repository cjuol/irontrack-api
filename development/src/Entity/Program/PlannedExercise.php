<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Entity\Catalog\Exercise;
use App\Repository\Program\PlannedExerciseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlannedExerciseRepository::class)]
#[ORM\Table(name: 'planned_exercises')]
class PlannedExercise
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ExerciseBlock::class, inversedBy: 'plannedExercises')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ExerciseBlock $exerciseBlock;

    #[ORM\ManyToOne(targetEntity: Exercise::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Exercise $exercise;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    /** Instrucciones adicionales del entrenador para este ejercicio en este bloque. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * Indica si este ejercicio forma parte de una superserie o triserie.
     * Se usa junto con supersetGroup para agrupar los ejercicios en la UI.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isSuperset = false;

    /**
     * Letra identificadora del grupo de superserie ('A', 'B', 'C'...).
     * Null si no pertenece a ninguna superserie.
     */
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $supersetGroup = null;

    /** @var Collection<int, PlannedSet> */
    #[ORM\OneToMany(mappedBy: 'plannedExercise', targetEntity: PlannedSet::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $plannedSets;

    public function __construct()
    {
        $this->id          = Uuid::v4();
        $this->plannedSets = new ArrayCollection();
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

    public function getExercise(): Exercise
    {
        return $this->exercise;
    }

    public function setExercise(Exercise $exercise): static
    {
        $this->exercise = $exercise;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function isSuperset(): bool
    {
        return $this->isSuperset;
    }

    public function setIsSuperset(bool $isSuperset): static
    {
        $this->isSuperset = $isSuperset;
        return $this;
    }

    public function getSupersetGroup(): ?string
    {
        return $this->supersetGroup;
    }

    public function setSupersetGroup(?string $supersetGroup): static
    {
        $this->supersetGroup = $supersetGroup;
        return $this;
    }

    /** @return Collection<int, PlannedSet> */
    public function getPlannedSets(): Collection
    {
        return $this->plannedSets;
    }

    public function addPlannedSet(PlannedSet $set): static
    {
        if (!$this->plannedSets->contains($set)) {
            $this->plannedSets->add($set);
            $set->setPlannedExercise($this);
        }
        return $this;
    }
}