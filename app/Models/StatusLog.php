<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_request_id',
    'changed_by',
    'old_status',
    'new_status',
])]
class StatusLog extends Model
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
            'old_status' => ServiceRequestStatus::class,
            'new_status' => ServiceRequestStatus::class,
        ];
    }

    /**
     * Permohonan layanan yang mengalami perubahan status.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Pengguna/petugas yang melakukan perubahan status.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Alias relasi pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->changedBy();
    }
}
