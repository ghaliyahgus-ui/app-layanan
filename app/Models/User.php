<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'address', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Permohonan layanan yang diajukan oleh pemohon ini.
     */
    public function serviceRequestsAsApplicant(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'applicant_id');
    }

    /**
     * Permohonan layanan yang ditangani oleh petugas ini.
     */
    public function serviceRequestsAsOfficer(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'officer_id');
    }

    /**
     * Log perubahan status yang dilakukan oleh pengguna ini.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(StatusLog::class, 'changed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isOfficer(): bool
    {
        return $this->role === UserRole::Officer;
    }

    public function isLeader(): bool
    {
        return $this->role === UserRole::Leader;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }
}
