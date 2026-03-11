<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LogSetRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        public readonly float $weightKg = 0.0,
        #[Assert\NotNull]
        #[Assert\Positive]
        public readonly int $reps = 0,
        #[Assert\Range(min: 0, max: 10)]
        public readonly ?int $rir = null,
        public readonly bool $toFailure = false,
        #[Assert\Uuid]
        public readonly ?string $plannedSetId = null,
    ) {}
}
