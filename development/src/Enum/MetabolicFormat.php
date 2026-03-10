<?php

declare(strict_types=1);

namespace App\Enum;

enum MetabolicFormat: string
{
    case AMRAP             = 'amrap';              // As Many Rounds As Possible
    case EMOM              = 'emom';               // Every Minute On the Minute
    case ROUNDS_FOR_TIME   = 'rounds_for_time';    // X rondas a máxima velocidad
    case POWER_INTERVALS   = 'power_intervals';    // Intervalos de potencia
    case TEST              = 'test';               // Semana de test / evaluación

    public function label(): string
    {
        return match($this) {
            self::AMRAP           => 'AMRAP',
            self::EMOM            => 'EMOM',
            self::ROUNDS_FOR_TIME => 'Rondas por tiempo',
            self::POWER_INTERVALS => 'Intervalos de potencia',
            self::TEST            => 'Test',
        };
    }
}