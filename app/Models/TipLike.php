<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'tip_id',
        'user_id',
        'device_id',
    ];

    /**
     * Get the tip that owns the like.
     */
    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }

    /**
     * Get the user that owns the like.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
