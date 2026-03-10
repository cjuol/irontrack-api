<?php

declare(strict_types=1);

namespace App\Entity\Log;

use App\Entity\Program\WeeklyMetabolicPlan;
use App\Repository\Log\MetabolicEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MetabolicEntryRepository::class)]
#[ORM\Table(name: 'metabolic_entries')]
class MetabolicEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: WorkoutSession::class, inversedBy: 'metabolicEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WorkoutSession $workoutSession;

    /**
     * Plan metabólico semanal de referencia.
     * Null si el bloque no corresponde a un plan programado.
     */
    #[ORM\ManyToOne(targetEntity: WeeklyMetabolicPlan::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?WeeklyMetabolicPlan $weeklyMetabolicPlan = null;

    /** Semana del mesociclo en que se realizó (1-5). Desnormalizado para facilitar queries. */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weekNumber = null;

    /** Rondas completadas (para formatos AMRAP y Rondas). */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $roundsCompleted = null;

    /** Tiempo total en segundos (para formatos a tiempo). */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeSeconds = null;

    /**
     * Resultado del bloque (texto libre).
     * Especialmente útil para la semana de test (semana 5): anotar marca obtenida.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $result = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
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

    public function getWeeklyMetabolicPlan(): ?WeeklyMetabolicPlan
    {
        return $this->weeklyMetabolicPlan;
    }

    public function setWeeklyMetabolicPlan(?WeeklyMetabolicPlan $weeklyMetabolicPlan): static
    {
        $this->weeklyMetabolicPlan = $weeklyMetabolicPlan;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getWeekNumber(): ?int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(?int $weekNumber): static
    {
        $this->weekNumber = $weekNumber;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getRoundsCompleted(): ?int
    {
        return $this->roundsCompleted;
    }

    public function setRoundsCompleted(?int $roundsCompleted): static
    {
        $this->roundsCompleted = $roundsCompleted;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getTimeSeconds(): ?int
    {
        return $this->timeSeconds;
    }

    public function setTimeSeconds(?int $timeSeconds): static
    {
        $this->timeSeconds = $timeSeconds;
        return $this;
    }

    #[Groups(['session:read'])]
    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;
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
}