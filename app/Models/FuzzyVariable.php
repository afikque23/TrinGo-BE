<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuzzyVariable extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_config_id',
        'var_key',
        'low_a', 'low_b', 'low_c',
        'med_a', 'med_b', 'med_c',
        'high_a', 'high_b', 'high_c',
    ];

    protected $casts = [
        'low_a' => 'float',
        'low_b' => 'float',
        'low_c' => 'float',
        'med_a' => 'float',
        'med_b' => 'float',
        'med_c' => 'float',
        'high_a' => 'float',
        'high_b' => 'float',
        'high_c' => 'float',
    ];

    public function componentConfig(): BelongsTo
    {
        return $this->belongsTo(ComponentConfig::class);
    }
}
