<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LogMetabolicRequest
{
    public function __construct(
        #[Assert\Uuid]
        public readonly ?string $weeklyMetabolicPlanId = null,
        #[Assert\Range(min: 1, max: 10)]
        public readonly ?int $weekNumber = null,
        #[Assert\PositiveOrZero]
        public readonly ?int $rounds = null,
        #[Assert\Positive]
        public readonly ?int $timeSeconds = null,
        public readonly ?string $result = null,
        public readonly ?string $notes = null,
    ) {}
}
