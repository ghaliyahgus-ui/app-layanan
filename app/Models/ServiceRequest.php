<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'request_number',
    'applicant_id',
    'officer_id',
    'service_type_id',
    'letter_number',
    'request_date',
    'activity_date',
    'location',
    'notes',
    'status',
])]
class ServiceRequest extends Model
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
            'request_date' => 'date',
            'activity_date' => 'date',
            'status' => ServiceRequestStatus::class,
        ];
    }

    /**
     * Pemohon yang mengajukan permohonan.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * Petugas yang menangani atau memproses permohonan.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * Jenis layanan yang diminta (Pemangkasan, Perapian, Peminjaman Lahan).
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Dokumen pendukung (KTP, Surat Permohonan, dll).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Jadwal pelaksanaan permohonan.
     */
    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
    }

    /**
     * Surat izin peminjaman lahan (jika jenis layanan memerlukan izin).
     */
    public function permit(): HasOne
    {
        return $this->hasOne(Permit::class);
    }

    /**
     * Catatan riwayat perubahan status permohonan.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLog::class);
    }
}
