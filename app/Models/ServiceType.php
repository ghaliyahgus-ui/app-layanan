<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'requires_permit', 'is_active'])]
class ServiceType extends Model
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
            'requires_permit' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Daftar permohonan dengan jenis layanan ini.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    /**
     * Daftar tarif untuk jenis layanan ini.
     */
    public function tariffs(): HasMany
    {
        return $this->hasMany(Tariff::class);
    }
}
