<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'fcm_token',
        'device_type',
        'device_name',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relasi ke User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope hanya token aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope berdasarkan user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope berdasarkan device.
     */
    public function scopeForDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    /**
     * Register atau update FCM token.
     */
    public static function registerToken(array $data): self
    {
        $conditions = [];

        if (!empty($data['user_id'])) {
            $conditions['user_id'] = $data['user_id'];
        }
        if (!empty($data['device_id'])) {
            $conditions['device_id'] = $data['device_id'];
        }

        // Nonaktifkan token lama untuk device yang sama
        if (!empty($conditions)) {
            static::where($conditions)->update(['is_active' => false]);
        }

        // Buat atau update token baru
        return static::updateOrCreate(
            array_merge($conditions, ['fcm_token' => $data['fcm_token']]),
            [
                'fcm_token' => $data['fcm_token'],
                'device_type' => $data['device_type'] ?? 'android',
                'device_name' => $data['device_name'] ?? null,
                'is_active' => true,
                'last_used_at' => now(),
            ]
        );
    }

    /**
     * Ambil semua FCM token aktif untuk user tertentu.
     */
    public static function getTokensForUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->active()
            ->pluck('fcm_token')
            ->toArray();
    }

    /**
     * Ambil semua FCM token aktif untuk device tertentu.
     */
    public static function getTokensForDevice(string $deviceId): array
    {
        return static::where('device_id', $deviceId)
            ->active()
            ->pluck('fcm_token')
            ->toArray();
    }
}
