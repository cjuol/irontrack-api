<?php

declare(strict_types=1);

namespace App\Enum;

enum SyncType: string
{
    case STEPS   = 'steps';
    case WORKOUT = 'workout';
    case SLEEP   = 'sleep';
    case HRV     = 'hrv';

    public function label(): string
    {
        return match($this) {
            self::STEPS   => 'Pasos',
            self::WORKOUT => 'Entrenamiento',
            self::SLEEP   => 'Sueño',
            self::HRV     => 'HRV',
        };
    }
}