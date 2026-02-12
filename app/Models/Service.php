<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_type_id',
        'service_date',
        'odometer_km',
        'cost',
        'workshop_name',
        'notes',
        'receipt_image',
    ];

    protected $casts = [
        'service_date' => 'date',
        'odometer_km' => 'integer',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the vehicle that owns the service.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the service type of the service.
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Scope a query to filter by vehicle.
     */
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Scope a query to order by date descending.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('service_date', 'desc');
    }
}
