<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    protected $fillable = [
        'user_id', 'motor_id', 'fingerprint',
        'content', 'reuse_count', 'last_used_at',
    ];

    protected $casts = [
        'content'      => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Apakah cache masih valid (belum expired)
     * Default: 3 hari
     */
    public function isValid(int $days = 3): bool
    {
        return $this->created_at->diffInDays(now()) < $days;
    }

    /**
     * Tandai sebagai dipakai ulang
     */
    public function markReused(): void
    {
        $this->increment('reuse_count');
        $this->update(['last_used_at' => now()]);
    }
}
