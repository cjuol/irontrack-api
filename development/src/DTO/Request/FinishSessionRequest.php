<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class FinishSessionRequest
{
    public function __construct(
        #[Assert\Range(min: 1, max: 10)]
        public readonly ?int $perceivedEffort = null,
        public readonly ?string $notes = null,
    ) {}
}
