<?php

declare(strict_types=1);

namespace App\Entity\Program;

use App\Entity\User;
use App\Repository\Program\MesocycleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MesocycleRepository::class)]
#[ORM\Table(name: 'mesocycles')]
class Mesocycle
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'mesocycles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 200)]
    private string $name;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $endDate;

    #[ORM\Column(type: 'integer')]
    private int $numWeeks;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $objective = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    /**
     * Objetivo de pasos en días de entrenamiento.
     * Varía por mesociclo: M13=15000, M14=12000, M15=12000, M16=10000.
     */
    #[ORM\Column(type: 'integer')]
    private int $stepGoalTrainingDay = 10000;

    /**
     * Objetivo de pasos en días de descanso o descanso activo.
     * Varía por mesociclo: M13=10000, M14=15000, M15=12000, M16=12000.
     */
    #[ORM\Column(type: 'integer')]
    private int $stepGoalRestDay = 12000;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, SessionTemplate> */
    #[ORM\OneToMany(mappedBy: 'mesocycle', targetEntity: SessionTemplate::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $sessionTemplates;

    public function __construct()
    {
        $this->id               = Uuid::v4();
        $this->createdAt        = new \DateTimeImmutable();
        $this->sessionTemplates = new ArrayCollection();
    }

    #[Groups(['mesocycle:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getNumWeeks(): int
    {
        return $this->numWeeks;
    }

    public function setNumWeeks(int $numWeeks): static
    {
        $this->numWeeks = $numWeeks;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getStepGoalTrainingDay(): int
    {
        return $this->stepGoalTrainingDay;
    }

    public function setStepGoalTrainingDay(int $stepGoalTrainingDay): static
    {
        $this->stepGoalTrainingDay = $stepGoalTrainingDay;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getStepGoalRestDay(): int
    {
        return $this->stepGoalRestDay;
    }

    public function setStepGoalRestDay(int $stepGoalRestDay): static
    {
        $this->stepGoalRestDay = $stepGoalRestDay;
        return $this;
    }

    #[Groups(['mesocycle:read'])]
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, SessionTemplate> */
    public function getSessionTemplates(): Collection
    {
        return $this->sessionTemplates;
    }

    public function addSessionTemplate(SessionTemplate $template): static
    {
        if (!$this->sessionTemplates->contains($template)) {
            $this->sessionTemplates->add($template);
            $template->setMesocycle($this);
        }
        return $this;
    }

    /**
     * Calcula el número de semana (1-based) para una fecha dada dentro del mesociclo.
     * Devuelve null si la fecha está fuera del rango del mesociclo.
     */
    public function getWeekNumber(\DateTimeImmutable $date): ?int
    {
        if ($date < $this->startDate || $date > $this->endDate) {
            return null;
        }

        $days = (int) $this->startDate->diff($date)->days;
        return (int) floor($days / 7) + 1;
    }

    /**
     * Indica si el mesociclo está activo en la fecha dada.
     */
    public function isActiveOn(\DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }
}