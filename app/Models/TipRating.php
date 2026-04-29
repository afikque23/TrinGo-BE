<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipRating extends Model
{
    use HasFactory;

    protected $table = 'tip_ratings';

    protected $fillable = [
        'tip_id',
        'user_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the tip that owns the rating.
     */
    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }

    /**
     * Get the user that owns the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
