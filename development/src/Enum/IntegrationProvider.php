<?php

declare(strict_types=1);

namespace App\Enum;

enum IntegrationProvider: string
{
    case FITBIT = 'fitbit';
    case GARMIN = 'garmin';
    case STRAVA = 'strava';

    public function label(): string
    {
        return match($this) {
            self::FITBIT => 'Fitbit',
            self::GARMIN => 'Garmin',
            self::STRAVA => 'Strava',
        };
    }
}