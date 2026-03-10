<?php

declare(strict_types=1);

namespace App\Enum;

enum SessionType: string
{
    case HYBRID     = 'hybrid';     // Fuerza + Metabólico A
    case MIXED      = 'mixed';      // Fuerza + Cardio
    case STRENGTH   = 'strength';   // Solo Fuerza
    case RESISTANCE = 'resistance'; // Metabólico B + Cardio

    public function label(): string
    {
        return match($this) {
            self::HYBRID     => 'Híbrida',
            self::MIXED      => 'Mixta',
            self::STRENGTH   => 'Estructural',
            self::RESISTANCE => 'Resistencia',
        };
    }
}