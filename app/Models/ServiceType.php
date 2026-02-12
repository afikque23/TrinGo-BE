<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all services for this service type.
     */
    public function services(): HasMany
    {
        return $this->hasMany(ServiceHistory::class, 'service_type_id');
    }

    /**
     * Get all schedules for this service type.
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class, 'service_type_id');
    }

    /**
     * Scope untuk filter hanya service type yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $isActive)
    {
        return $query->where('is_active', $isActive);
    }
}
