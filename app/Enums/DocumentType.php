<?php

namespace App\Enums;

enum DocumentType: string
{
    case IdCard = 'id_card';
    case RequestLetter = 'request_letter';

    public function label(): string
    {
        return match ($this) {
            self::IdCard => 'Foto KTP',
            self::RequestLetter => 'Surat Permohonan',
        };
    }
}
