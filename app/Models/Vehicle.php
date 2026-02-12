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
        'title',
        'make',
        'model',
        'year',
        'tipe_motor',
        'vin',
        'odometer',
        'license_plate',
        'color',
        'photo_url',
        'is_primary',
    ];

    protected $casts = [
        'year' => 'integer',
        'odometer' => 'integer',
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
