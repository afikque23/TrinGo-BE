<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendationCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'motor_type',
        'inputs',
        'scores',
        'statuses',
        'config_hash',
        'scores_hash',
        'prompt_hash',
        'model',
        'sections',
        'generated_at',
    ];

    protected $casts = [
        'inputs' => 'array',
        'scores' => 'array',
        'statuses' => 'array',
        'sections' => 'array',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
