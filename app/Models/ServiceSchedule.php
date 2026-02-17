<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'service_type_id',
        'service_name',
        'schedule_type',
        'target_km',
        'target_date',
        'reminder_option_id',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_km' => 'integer',
        'target_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vehicle that owns the schedule.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the service type for this schedule.
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * Get the reminder option for this schedule.
     */
    public function reminderOption(): BelongsTo
    {
        return $this->belongsTo(ReminderOption::class);
    }

    /**
     * Scope a query to only include active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include schedules by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('schedule_type', $type);
    }

    /**
     * Scope a query to only include schedules for a specific vehicle.
     */
    public function scopeForVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }
}
