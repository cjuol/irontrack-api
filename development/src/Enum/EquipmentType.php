<?php

declare(strict_types=1);

namespace App\Enum;

enum EquipmentType: string
{
    case BARBELL       = 'barbell';
    case DUMBBELL      = 'dumbbell';
    case CABLE         = 'cable';
    case MACHINE       = 'machine';
    case BODYWEIGHT    = 'bodyweight';
    case KETTLEBELL    = 'kettlebell';
    case RESISTANCE    = 'resistance_band';
    case SMITH         = 'smith_machine';
    case PULLEY        = 'pulley';
    case NONE          = 'none';

    public function label(): string
    {
        return match($this) {
            self::BARBELL    => 'Barra',
            self::DUMBBELL   => 'Mancuernas',
            self::CABLE      => 'Cable',
            self::MACHINE    => 'Máquina',
            self::BODYWEIGHT => 'Peso corporal',
            self::KETTLEBELL => 'Kettlebell',
            self::RESISTANCE => 'Banda elástica',
            self::SMITH      => 'Multipower',
            self::PULLEY     => 'Polea',
            self::NONE       => 'Sin material',
        };
    }
}
