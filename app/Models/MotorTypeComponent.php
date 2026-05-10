<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MotorTypeComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'motor_type',
        'maintenance_component_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(MaintenanceComponent::class, 'maintenance_component_id');
    }

    public function fuzzyConfig(): HasOne
    {
        return $this->hasOne(FuzzyComponentConfig::class);
    }
}
