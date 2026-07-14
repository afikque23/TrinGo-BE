<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'device_id',
        'title',
        'make',
        'model',
        'year',
        'tipe_motor',
        'kapasitas_cc',
        'transmisi',
        'vin',
        'odometer',
        'photo_url',
        'last_latitude',
        'last_longitude',
        'last_speed_kph',
        'last_heading_deg',
        'last_altitude',
        'last_accuracy_meters',
        'last_satellites',
        'last_hdop',
        'last_baro_ok',
        'last_baro_rel_alt_m',
        'last_grade_ratio',
        'last_grade_pct',
        'last_telemetry_at',
        'last_telemetry_received_at',
        'is_primary',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer' => 'integer',
        'last_latitude' => 'decimal:7',
        'last_longitude' => 'decimal:7',
        'last_speed_kph' => 'integer',
        'last_heading_deg' => 'integer',
        'last_altitude' => 'float',
        'last_accuracy_meters' => 'float',
        'last_satellites' => 'integer',
        'last_hdop' => 'float',
        'last_baro_ok' => 'boolean',
        'last_baro_rel_alt_m' => 'float',
        'last_grade_ratio' => 'float',
        'last_grade_pct' => 'float',
        'last_telemetry_at' => 'datetime',
        'last_telemetry_received_at' => 'datetime',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the owner of the vehicle.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get users who have access to this vehicle.
     */
    public function sharedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'vehicle_user')
                    ->withPivot('role', 'granted_at', 'expires_at', 'is_active')
                    ->withTimestamps();
    }

    /**
     * Get the trips for the vehicle.
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Get the service histories for the vehicle.
     */
    public function serviceHistories(): HasMany
    {
        return $this->hasMany(ServiceHistory::class);
    }

    /**
     * Get the services for the vehicle.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the fuel logs for the vehicle.
     */
    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    /**
     * Get the reminders for the vehicle.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Get the service schedules for the vehicle.
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    /**
     * Get the documents for the vehicle.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the service intervals for the vehicle.
     */
    public function serviceIntervals(): HasMany
    {
        return $this->hasMany(ServiceInterval::class);
    }
}
