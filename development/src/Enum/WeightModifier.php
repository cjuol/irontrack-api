<?php

declare(strict_types=1);

namespace App\Enum;

enum WeightModifier: string
{
    case KEEP     = 'keep';
    case INCREASE = 'increase';
    case DECREASE = 'decrease';

    public function label(): string
    {
        return match($this) {
            self::KEEP     => 'Mantener peso',
            self::INCREASE => 'Subir peso',
            self::DECREASE => 'Bajar peso',
        };
    }
}