<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Enum\BlockType;
use App\Repository\Program\ExerciseBlockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExerciseBlockRepository::class)]
#[ORM\Table(name: 'exercise_blocks')]
class ExerciseBlock
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: SessionTemplate::class, inversedBy: 'exerciseBlocks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private SessionTemplate $sessionTemplate;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', enumType: BlockType::class)]
    private BlockType $type;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    /** @var Collection<int, PlannedExercise> */
    #[ORM\OneToMany(mappedBy: 'exerciseBlock', targetEntity: PlannedExercise::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $plannedExercises;

    /** @var Collection<int, WeeklyMetabolicPlan> */
    #[ORM\OneToMany(mappedBy: 'exerciseBlock', targetEntity: WeeklyMetabolicPlan::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['weekNumber' => 'ASC'])]
    private Collection $weeklyMetabolicPlans;

    /** @var Collection<int, WeeklyCardioPlan> */
    #[ORM\OneToMany(mappedBy: 'exerciseBlock', targetEntity: WeeklyCardioPlan::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['weekNumber' => 'ASC'])]
    private Collection $weeklyCardioPlans;

    public function __construct()
    {
        $this->id                   = Uuid::v4();
        $this->plannedExercises     = new ArrayCollection();
        $this->weeklyMetabolicPlans = new ArrayCollection();
        $this->weeklyCardioPlans    = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSessionTemplate(): SessionTemplate
    {
        return $this->sessionTemplate;
    }

    public function setSessionTemplate(SessionTemplate $sessionTemplate): static
    {
        $this->sessionTemplate = $sessionTemplate;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): BlockType
    {
        return $this->type;
    }

    public function setType(BlockType $type): static
    {
        $this->type = $type;
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

    /** @return Collection<int, PlannedExercise> */
    public function getPlannedExercises(): Collection
    {
        return $this->plannedExercises;
    }

    public function addPlannedExercise(PlannedExercise $exercise): static
    {
        if (!$this->plannedExercises->contains($exercise)) {
            $this->plannedExercises->add($exercise);
            $exercise->setExerciseBlock($this);
        }
        return $this;
    }

    /** @return Collection<int, WeeklyMetabolicPlan> */
    public function getWeeklyMetabolicPlans(): Collection
    {
        return $this->weeklyMetabolicPlans;
    }

    /** @return Collection<int, WeeklyCardioPlan> */
    public function getWeeklyCardioPlans(): Collection
    {
        return $this->weeklyCardioPlans;
    }

    public function getMetabolicPlanForWeek(int $week): ?WeeklyMetabolicPlan
    {
        foreach ($this->weeklyMetabolicPlans as $plan) {
            if ($plan->getWeekNumber() === $week) {
                return $plan;
            }
        }
        return null;
    }

    public function getCardioPlanForWeek(int $week): ?WeeklyCardioPlan
    {
        foreach ($this->weeklyCardioPlans as $plan) {
            if ($plan->getWeekNumber() === $week) {
                return $plan;
            }
        }
        return null;
    }
}