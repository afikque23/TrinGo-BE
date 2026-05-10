<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuzzyComponentConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'motor_type_component_id',
        'warn_score',
        'critical_score',
        'config',
        'version',
    ];

    protected $casts = [
        'warn_score' => 'integer',
        'critical_score' => 'integer',
        'config' => 'array',
        'version' => 'integer',
    ];

    public function motorTypeComponent(): BelongsTo
    {
        return $this->belongsTo(MotorTypeComponent::class, 'motor_type_component_id');
    }
}
