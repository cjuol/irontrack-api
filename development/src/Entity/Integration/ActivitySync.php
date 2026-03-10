<?php

declare(strict_types=1);

namespace App\Entity\Integration;

use App\Entity\Log\TrainingDay;
use App\Entity\Log\WorkoutSession;
use App\Enum\SyncType;
use App\Repository\Integration\ActivitySyncRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Registro de un dato sincronizado desde un servicio externo.
 * Conserva el rawData original para poder re-procesar si cambia la lógica.
 */
#[ORM\Entity(repositoryClass: ActivitySyncRepository::class)]
#[ORM\Table(name: 'activity_syncs')]
#[ORM\UniqueConstraint(name: 'uq_sync_account_external', columns: ['integration_account_id', 'external_id', 'sync_type'])]
class ActivitySync
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: IntegrationAccount::class, inversedBy: 'activitySyncs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private IntegrationAccount $integrationAccount;

    /** Día al que se asocia este sync (para pasos, sueño, HRV). */
    #[ORM\ManyToOne(targetEntity: TrainingDay::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?TrainingDay $trainingDay = null;

    /** Sesión a la que se asocia este sync (para workouts). */
    #[ORM\ManyToOne(targetEntity: WorkoutSession::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?WorkoutSession $workoutSession = null;

    /** ID de la actividad en la plataforma externa. */
    #[ORM\Column(type: 'string', length: 255)]
    private string $externalId;

    /** Payload completo recibido de la API externa, sin procesar. */
    #[ORM\Column(type: 'json')]
    private array $rawData = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $syncedAt;

    #[ORM\Column(type: 'string', enumType: SyncType::class)]
    private SyncType $syncType;

    public function __construct()
    {
        $this->id       = Uuid::v4();
        $this->syncedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getIntegrationAccount(): IntegrationAccount
    {
        return $this->integrationAccount;
    }

    public function setIntegrationAccount(IntegrationAccount $integrationAccount): static
    {
        $this->integrationAccount = $integrationAccount;
        return $this;
    }

    public function getTrainingDay(): ?TrainingDay
    {
        return $this->trainingDay;
    }

    public function setTrainingDay(?TrainingDay $trainingDay): static
    {
        $this->trainingDay = $trainingDay;
        return $this;
    }

    public function getWorkoutSession(): ?WorkoutSession
    {
        return $this->workoutSession;
    }

    public function setWorkoutSession(?WorkoutSession $workoutSession): static
    {
        $this->workoutSession = $workoutSession;
        return $this;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function setRawData(array $rawData): static
    {
        $this->rawData = $rawData;
        return $this;
    }

    public function getSyncedAt(): \DateTimeImmutable
    {
        return $this->syncedAt;
    }

    public function getSyncType(): SyncType
    {
        return $this->syncType;
    }

    public function setSyncType(SyncType $syncType): static
    {
        $this->syncType = $syncType;
        return $this;
    }
}