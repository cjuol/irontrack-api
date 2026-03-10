<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Log\TrainingDay;
use App\Entity\Program\Mesocycle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Mesocycle::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['startDate' => 'DESC'])]
    private Collection $mesocycles;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TrainingDay::class, cascade: ['persist', 'remove'])]
    private Collection $trainingDays;

    public function __construct()
    {
        $this->id           = Uuid::v4();
        $this->createdAt    = new \DateTimeImmutable();
        $this->mesocycles   = new ArrayCollection();
        $this->trainingDays = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
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

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles   = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Mesocycle> */
    public function getMesocycles(): Collection
    {
        return $this->mesocycles;
    }

    /** @return Collection<int, TrainingDay> */
    public function getTrainingDays(): Collection
    {
        return $this->trainingDays;
    }
}