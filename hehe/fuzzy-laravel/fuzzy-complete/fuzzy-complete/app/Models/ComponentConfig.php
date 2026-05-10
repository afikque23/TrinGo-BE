<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentConfig extends Model
{
    protected $fillable = [
        'motor_type_id', 'name', 'status',
        'warn', 'critical', 'reset_interval',
        'active_vars', 'is_active', 'is_custom', 'notes',
    ];

    protected $casts = [
        'active_vars' => 'array',
        'is_active'   => 'boolean',
        'is_custom'   => 'boolean',
    ];

    public function motorType(): BelongsTo
    {
        return $this->belongsTo(MotorType::class);
    }

    public function fuzzyVariables(): HasMany
    {
        return $this->hasMany(FuzzyVariable::class);
    }

    public function fuzzyRules(): HasMany
    {
        return $this->hasMany(FuzzyRule::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(FuzzyAuditLog::class, 'component_name', 'name');
    }
}
