<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipSearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'keyword',
        'source',
    ];

    /**
     * Log a normalized keyword for recommendation signals.
     */
    public static function logKeyword(?int $userId, ?string $deviceId, ?string $keyword, string $source = 'tips_index'): void
    {
        $normalized = Tip::normalizeKeyword($keyword);

        if ($normalized === '') {
            return;
        }

        // Require at least one identity.
        if (!$userId && (!$deviceId || trim($deviceId) === '')) {
            return;
        }

        $query = static::query()->where('keyword', $normalized);
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->where('device_id', $deviceId);
        }

        // Basic dedupe: do not spam identical keywords within a short window.
        if ($query->where('created_at', '>=', now()->subMinutes(10))->exists()) {
            return;
        }

        static::query()->create([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'keyword' => $normalized,
            'source' => $source,
        ]);
    }
}
