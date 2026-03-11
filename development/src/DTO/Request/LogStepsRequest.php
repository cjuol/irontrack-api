<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class LogStepsRequest
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        public readonly int $steps = 0,
    ) {}
}
