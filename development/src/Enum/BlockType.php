<?php

declare(strict_types=1);

namespace App\Enum;

enum BlockType: string
{
    case STRENGTH   = 'strength';
    case METABOLIC  = 'metabolic';
    case CARDIO     = 'cardio';

    public function label(): string
    {
        return match($this) {
            self::STRENGTH  => 'Fuerza',
            self::METABOLIC => 'Metabólico',
            self::CARDIO    => 'Cardio',
        };
    }
}