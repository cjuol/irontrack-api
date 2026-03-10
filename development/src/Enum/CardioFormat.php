<?php

declare(strict_types=1);

namespace App\Enum;

enum CardioFormat: string
{
    case WALK        = 'walk';
    case JOG         = 'jog';
    case INTERVALS   = 'intervals';
    case CONTINUOUS  = 'continuous';
    case SIMULATION  = 'simulation'; // Simulación de prueba Hyrox

    public function label(): string
    {
        return match($this) {
            self::WALK       => 'Caminata',
            self::JOG        => 'Trote suave',
            self::INTERVALS  => 'Intervalos',
            self::CONTINUOUS => 'Cardio continuo',
            self::SIMULATION => 'Simulación',
        };
    }
}