<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'filled_by',
        'filled_at',
        'liters',
        'price_cents',
        'currency',
        'odometer',
        'station_name',
        'fuel_efficiency',
        'notes',
    ];

    protected $casts = [
        'filled_at' => 'datetime',
        'liters' => 'decimal:3',
        'price_cents' => 'integer',
        'odometer' => 'integer',
        'fuel_efficiency' => 'decimal:2',
    ];

    /**
     * Get the vehicle that owns the fuel log.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who filled the fuel.
     */
    public function filledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by');
    }

    /**
     * Get the cost in currency format
     */
    public function getCostAttribute(): float
    {
        return $this->price_cents / 100;
    }

    /**
     * Set the cost (automatically converts to cents)
     */
    public function setCostAttribute($value): void
    {
        $this->attributes['price_cents'] = $value * 100;
    }

    /**
     * Calculate fuel efficiency based on distance and liters
     */
    public function calculateEfficiency(float $distanceKm): void
    {
        if ($this->liters > 0) {
            $this->fuel_efficiency = $distanceKm / $this->liters;
            $this->save();
        }
    }
}
