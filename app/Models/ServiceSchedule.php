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
        'interval_value',
        'last_service_mileage',
        'last_service_date',
        'target_km',
        'target_date',
        'reminder_option_id',
        'reminder_threshold',
        'reminder_sent',
        'reminder_sent_at',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'interval_value' => 'integer',
        'last_service_mileage' => 'integer',
        'last_service_date' => 'date',
        'target_km' => 'integer',
        'target_date' => 'date',
        'reminder_threshold' => 'integer',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
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

    /**
     * Accessor for interval_value - auto-calculate if 0 or null
     * Safety net to ensure interval_value is never 0
     */
    public function getIntervalValueAttribute($value)
    {
        // If interval_value is already set and valid, return it
        if ($value && $value > 0) {
            return $value;
        }

        // Auto-calculate based on schedule_type
        if ($this->schedule_type == 'km' && $this->target_km) {
            // For mileage-based: interval = target_km - last_service_mileage
            return $this->target_km - ($this->last_service_mileage ?? 0);
        } elseif ($this->schedule_type == 'time' && $this->target_date && $this->last_service_date) {
            // For time-based: interval = days between dates
            return \Carbon\Carbon::parse($this->target_date)
                ->diffInDays(\Carbon\Carbon::parse($this->last_service_date));
        }

        return $value ?? 0;
    }
}
