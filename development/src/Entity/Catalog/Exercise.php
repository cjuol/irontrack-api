<?php

declare(strict_types=1);

namespace App\Entity\Catalog;

use App\Enum\EquipmentType;
use App\Repository\Catalog\ExerciseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
#[ORM\Table(name: 'exercises')]
class Exercise
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(type: 'string', enumType: EquipmentType::class)]
    private EquipmentType $equipment = EquipmentType::NONE;

    /**
     * Músculos principales que trabaja el ejercicio.
     *
     * @var Collection<int, MuscleGroup>
     */
    #[ORM\ManyToMany(targetEntity: MuscleGroup::class)]
    #[ORM\JoinTable(name: 'exercise_primary_muscles')]
    private Collection $primaryMuscles;

    /**
     * Músculos secundarios / sinergistas.
     *
     * @var Collection<int, MuscleGroup>
     */
    #[ORM\ManyToMany(targetEntity: MuscleGroup::class)]
    #[ORM\JoinTable(name: 'exercise_secondary_muscles')]
    private Collection $secondaryMuscles;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id               = Uuid::v4();
        $this->createdAt        = new \DateTimeImmutable();
        $this->primaryMuscles   = new ArrayCollection();
        $this->secondaryMuscles = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): static
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;
        return $this;
    }

    public function getEquipment(): EquipmentType
    {
        return $this->equipment;
    }

    public function setEquipment(EquipmentType $equipment): static
    {
        $this->equipment = $equipment;
        return $this;
    }

    /** @return Collection<int, MuscleGroup> */
    public function getPrimaryMuscles(): Collection
    {
        return $this->primaryMuscles;
    }

    public function addPrimaryMuscle(MuscleGroup $muscle): static
    {
        if (!$this->primaryMuscles->contains($muscle)) {
            $this->primaryMuscles->add($muscle);
        }
        return $this;
    }

    /** @return Collection<int, MuscleGroup> */
    public function getSecondaryMuscles(): Collection
    {
        return $this->secondaryMuscles;
    }

    public function addSecondaryMuscle(MuscleGroup $muscle): static
    {
        if (!$this->secondaryMuscles->contains($muscle)) {
            $this->secondaryMuscles->add($muscle);
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}