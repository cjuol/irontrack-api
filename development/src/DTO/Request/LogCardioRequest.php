<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LogCardioRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $type = '',
        #[Assert\NotNull]
        #[Assert\Positive]
        public readonly int $durationSeconds = 0,
        #[Assert\Positive]
        public readonly ?float $distanceMeters = null,
        #[Assert\Positive]
        public readonly ?float $avgSpeedKmh = null,
        #[Assert\Range(min: 0, max: 30)]
        public readonly ?float $inclinePct = null,
        public readonly ?string $notes = null,
        #[Assert\Uuid]
        public readonly ?string $weeklyCardioPlanId = null,
    ) {}
}
