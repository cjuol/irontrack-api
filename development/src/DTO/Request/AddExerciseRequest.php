<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AddExerciseRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $exerciseId = '',
        #[Assert\Uuid]
        public readonly ?string $plannedExerciseId = null,
    ) {}
}
