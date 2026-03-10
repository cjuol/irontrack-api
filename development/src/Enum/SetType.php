<?php

declare(strict_types=1);

namespace App\Enum;

enum SetType: string
{
    case NORMAL        = 'normal';
    case TOP_SET       = 'top_set';       // TS — serie de máxima intensidad
    case AMRAP         = 'amrap';         // As Many Reps As Possible
    case BACK_OFF      = 'back_off';      // BO — series submáximas tras Top Set
    case DESCENDING    = 'descending';    // Drop Set — reducción de carga serie a serie
    case SET_COUNTDOWN = 'set_countdown'; // SCD — pirámide descendente de repeticiones
    case CLUSTER       = 'cluster';       // Microdescansos dentro de la serie
    case REST_PAUSE    = 'rest_pause';    // Breve pausa y continuar hasta fallo
    case SUPERSET      = 'superset';      // Dos ejercicios sin descanso entre sí
    case POLIQUIN      = 'poliquin';      // Triserie Charles Poliquin
    case PARTIAL_FULL  = 'partial_full';  // Parciales + completas en la misma serie
    case PAP           = 'pap';           // Post-Activation Potentiation

    public function label(): string
    {
        return match($this) {
            self::NORMAL        => 'Normal',
            self::TOP_SET       => 'Top Set (TS)',
            self::AMRAP         => 'AMRAP',
            self::BACK_OFF      => 'Back-Off (BO)',
            self::DESCENDING    => 'Descendente',
            self::SET_COUNTDOWN => 'Set CountDown (SCD)',
            self::CLUSTER       => 'Clúster',
            self::REST_PAUSE    => 'Rest-Pause',
            self::SUPERSET      => 'Superserie',
            self::POLIQUIN      => 'Triserie Poliquin',
            self::PARTIAL_FULL  => 'Parciales + Completas',
            self::PAP           => 'PAP',
        };
    }
}