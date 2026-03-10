<?php

declare(strict_types=1);

namespace App\Entity\Catalog;

use App\Repository\Catalog\MuscleGroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MuscleGroupRepository::class)]
#[ORM\Table(name: 'muscle_groups')]
class MuscleGroup
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $slug;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    #[Groups(['exercise:read'])]
    public function getId(): Uuid
    {
        return $this->id;
    }

    #[Groups(['exercise:read'])]
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    #[Groups(['exercise:read'])]
    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }
}
