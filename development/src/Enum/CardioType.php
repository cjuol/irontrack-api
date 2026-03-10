<?php

declare(strict_types=1);

namespace App\Enum;

enum CardioType: string
{
    case RUN  = 'run';
    case BIKE = 'bike';
    case ROW  = 'row';
    case SKI  = 'ski';
    case WALK = 'walk';
    case ROPE = 'rope';

    public function label(): string
    {
        return match($this) {
            self::RUN  => 'Carrera',
            self::BIKE => 'Bicicleta',
            self::ROW  => 'Remo',
            self::SKI  => 'Ski Erg',
            self::WALK => 'Caminata',
            self::ROPE => 'Comba',
        };
    }
}