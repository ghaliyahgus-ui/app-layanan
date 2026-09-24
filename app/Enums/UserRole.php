<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Officer = 'officer';
    case Leader = 'leader';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Officer => 'Petugas Layanan',
            self::Leader => 'Pimpinan',
            self::Client => 'Klien / Pemohon',
        };
    }
}
