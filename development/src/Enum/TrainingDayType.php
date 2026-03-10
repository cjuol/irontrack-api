<?php

declare(strict_types=1);

namespace App\Enum;

enum TrainingDayType: string
{
    case TRAINING    = 'training';
    case REST        = 'rest';
    case ACTIVE_REST = 'active_rest';

    public function label(): string
    {
        return match($this) {
            self::TRAINING    => 'Entrenamiento',
            self::REST        => 'Descanso',
            self::ACTIVE_REST => 'Descanso activo',
        };
    }

    public function isRest(): bool
    {
        return $this !== self::TRAINING;
    }
}