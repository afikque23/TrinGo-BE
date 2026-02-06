<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPoint extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'trip_id',
        'sequence',
        'latitude',
        'longitude',
        'altitude',
        'speed_kph',
        'accuracy_meters',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'altitude' => 'float',
        'speed_kph' => 'integer',
        'accuracy_meters' => 'float',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the trip that owns the trip point.
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
