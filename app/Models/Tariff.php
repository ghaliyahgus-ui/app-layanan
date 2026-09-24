<?php

namespace App\Models;

use App\Enums\TariffUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['service_type_id', 'amount', 'unit', 'is_active'])]
class Tariff extends Model
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
            'amount' => 'decimal:2',
            'unit' => TariffUnit::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Jenis layanan dari tarif ini.
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Pembayaran yang menggunakan tarif ini.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
