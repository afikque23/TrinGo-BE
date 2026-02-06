<?php

namespace App\Models;

<<<<<<< HEAD
class Trip
{
    // Model stub - properties: id, vehicle_id, start_at, end_at, distance_meters
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'started_by',
        'start_at',
        'end_at',
        'distance_meters',
        'start_odometer',
        'end_odometer',
        'avg_speed_kph',
        'max_speed_kph',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'distance_meters' => 'integer',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
        'avg_speed_kph' => 'decimal:2',
        'max_speed_kph' => 'decimal:2',
    ];

    /**
     * Get the vehicle that owns the trip.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who started the trip.
     */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    /**
     * Get the trip points for the trip.
     */
    public function points(): HasMany
    {
        return $this->hasMany(TripPoint::class)->orderBy('sequence');
    }

    /**
     * Calculate trip duration in minutes
     */
    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->start_at || !$this->end_at) {
            return null;
        }
        return $this->start_at->diffInMinutes($this->end_at);
    }

    /**
     * Get distance in kilometers
     */
    public function getDistanceKmAttribute(): ?float
    {
        if (!$this->distance_meters) {
            return null;
        }
        return round($this->distance_meters / 1000, 2);
    }
>>>>>>> a141ab55b3ee831814b4651bcc3ab00e6ae62169
}
