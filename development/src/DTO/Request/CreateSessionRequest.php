<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateSessionRequest
{
    public function __construct(
        #[Assert\Range(min: 1, max: 20)]
        public readonly ?int $templateSortOrder = null,
        #[Assert\Uuid]
        public readonly ?string $templateId = null,
    ) {}
}
