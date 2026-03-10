<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Enum\SessionType;
use App\Repository\Program\SessionTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SessionTemplateRepository::class)]
#[ORM\Table(name: 'session_templates')]
class SessionTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Mesocycle::class, inversedBy: 'sessionTemplates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Mesocycle $mesocycle;

    #[ORM\Column(type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(type: 'string', enumType: SessionType::class)]
    private SessionType $type;

    /** Posición dentro del mesociclo (1=Lunes, 2=Miércoles, etc.) */
    #[ORM\Column(type: 'integer')]
    private int $sortOrder;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /** @var Collection<int, ExerciseBlock> */
    #[ORM\OneToMany(mappedBy: 'sessionTemplate', targetEntity: ExerciseBlock::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $exerciseBlocks;

    public function __construct()
    {
        $this->id             = Uuid::v4();
        $this->exerciseBlocks = new ArrayCollection();
    }

    #[Groups(['mesocycle:sessions', 'training-day:read', 'session:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getMesocycle(): Mesocycle
    {
        return $this->mesocycle;
    }

    public function setMesocycle(Mesocycle $mesocycle): static
    {
        $this->mesocycle = $mesocycle;
        return $this;
    }

    #[Groups(['mesocycle:sessions', 'training-day:read', 'session:read'])]
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    #[Groups(['mesocycle:sessions', 'session:read'])]
    public function getType(): SessionType
    {
        return $this->type;
    }

    public function setType(SessionType $type): static
    {
        $this->type = $type;
        return $this;
    }

    #[Groups(['mesocycle:sessions'])]
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    #[Groups(['mesocycle:sessions', 'session:read'])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    /** @return Collection<int, ExerciseBlock> */
    #[Groups(['mesocycle:sessions'])]
    public function getExerciseBlocks(): Collection
    {
        return $this->exerciseBlocks;
    }

    public function addExerciseBlock(ExerciseBlock $block): static
    {
        if (!$this->exerciseBlocks->contains($block)) {
            $this->exerciseBlocks->add($block);
            $block->setSessionTemplate($this);
        }
        return $this;
    }
}