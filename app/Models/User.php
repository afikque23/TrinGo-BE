<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'refresh_token',
        'refresh_token_expires_at',
        'device_id',
        'device_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Generate refresh token
     */
    public function generateRefreshToken(int $days = 30): string
    {
        $token = bin2hex(random_bytes(64));
        $this->update([
            'refresh_token' => hash('sha256', $token),
            'refresh_token_expires_at' => now()->addDays($days),
        ]);
        return $token;
    }

    /**
     * Verify refresh token
     */
    public function verifyRefreshToken(string $token): bool
    {
        if (!$this->refresh_token || !$this->refresh_token_expires_at) {
            return false;
        }

        if ($this->refresh_token_expires_at->isPast()) {
            return false;
        }

        return hash_equals($this->refresh_token, hash('sha256', $token));
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(): void
    {
        $this->update([
            'refresh_token' => null,
            'refresh_token_expires_at' => null,
            'device_id' => null,
            'device_name' => null,
        ]);
    }

    /**
     * Update device info
     */
    public function updateDeviceInfo(?string $deviceId, ?string $deviceName): void
    {
        $this->update([
            'device_id' => $deviceId,
            'device_name' => $deviceName,
        ]);
    }

    /**
     * Relationships
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function primaryVehicle()
    {
        return $this->hasOne(Vehicle::class)->where('is_primary', true);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'started_by');
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class, 'filled_by');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function sharedVehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'vehicle_user')
            ->withPivot('role', 'granted_at', 'expires_at', 'is_active')
            ->withTimestamps();
    }
}
