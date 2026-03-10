<?php

declare(strict_types=1);

namespace App\Entity\Integration;

use App\Entity\User;
use App\Enum\IntegrationProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Cuenta de integración con servicio externo (Fitbit, Garmin, Strava).
 * Los tokens se almacenan cifrados — usar defuse/php-encryption en el setter.
 *
 * @see https://github.com/defuse/php-encryption
 */
#[ORM\Entity]
#[ORM\Table(name: 'integration_accounts')]
#[ORM\UniqueConstraint(name: 'uq_integration_user_provider', columns: ['user_id', 'provider'])]
class IntegrationAccount
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', enumType: IntegrationProvider::class)]
    private IntegrationProvider $provider;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $externalUserId = null;

    /** Almacenado cifrado. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $accessToken = null;

    /** Almacenado cifrado. */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $tokenExpiresAt = null;

    /**
     * Scopes autorizados por el usuario.
     *
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $scopes = [];

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ActivitySync> */
    #[ORM\OneToMany(mappedBy: 'integrationAccount', targetEntity: ActivitySync::class, cascade: ['persist', 'remove'])]
    private Collection $activitySyncs;

    public function __construct()
    {
        $this->id           = Uuid::v4();
        $this->createdAt    = new \DateTimeImmutable();
        $this->updatedAt    = new \DateTimeImmutable();
        $this->activitySyncs = new ArrayCollection();
    }

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

    public function getProvider(): IntegrationProvider
    {
        return $this->provider;
    }

    public function setProvider(IntegrationProvider $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function getExternalUserId(): ?string
    {
        return $this->externalUserId;
    }

    public function setExternalUserId(?string $externalUserId): static
    {
        $this->externalUserId = $externalUserId;
        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;
        $this->updatedAt   = new \DateTimeImmutable();
        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->tokenExpiresAt;
    }

    public function setTokenExpiresAt(?\DateTimeImmutable $tokenExpiresAt): static
    {
        $this->tokenExpiresAt = $tokenExpiresAt;
        return $this;
    }

    public function isTokenExpired(): bool
    {
        if ($this->tokenExpiresAt === null) {
            return false;
        }
        return $this->tokenExpiresAt <= new \DateTimeImmutable();
    }

    /** @return string[] */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): static
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, ActivitySync> */
    public function getActivitySyncs(): Collection
    {
        return $this->activitySyncs;
    }
}