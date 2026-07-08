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
        'has_fix',
        'latitude',
        'longitude',
        'altitude',
        'baro_rel_alt_m',
        'grade_pct',
        'speed_kph',
        'est_distance_m',
        'mpu_is_moving',
        'mpu_g_force',
        'accuracy_meters',
        'recorded_at',
    ];

    protected $casts = [
        'has_fix' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'altitude' => 'float',
        'baro_rel_alt_m' => 'float',
        'grade_pct' => 'float',
        'speed_kph' => 'integer',
        'est_distance_m' => 'float',
        'mpu_is_moving' => 'boolean',
        'mpu_g_force' => 'float',
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
