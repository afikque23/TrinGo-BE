<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuzzyConfigAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'motor_type',
        'motor_type_component_id',
        'maintenance_component_id',
        'admin_email',
        'component_label',
        'changed_field',
        'old_value',
        'new_value',
    ];

    protected $casts = [
        'motor_type_component_id' => 'integer',
        'maintenance_component_id' => 'integer',
    ];
}
