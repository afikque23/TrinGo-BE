<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuzzyAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'component_name',
        'motor_type',
        'field_changed',
        'old_value',
        'new_value',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
