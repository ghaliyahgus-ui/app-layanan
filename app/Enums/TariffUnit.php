<?php

namespace App\Enums;

enum TariffUnit: string
{
    case PerDay = 'per_day';
    case PerActivity = 'per_activity';

    public function label(): string
    {
        return match ($this) {
            self::PerDay => 'Per Hari',
            self::PerActivity => 'Per Kegiatan',
        };
    }
}
