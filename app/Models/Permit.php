<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'service_request_id',
    'permit_number',
    'issued_date',
    'valid_until',
    'file_path',
])]
class Permit extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'valid_until' => 'date',
        ];
    }

    /**
     * Permohonan layanan yang mendapatkan surat izin ini.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Pembayaran retribusi terkait surat izin ini.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
