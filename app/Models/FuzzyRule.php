<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuzzyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_config_id',
        'var1',
        'label1',
        'operator',
        'var2',
        'label2',
        'output',
        'weight',
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    public function componentConfig(): BelongsTo
    {
        return $this->belongsTo(ComponentConfig::class);
    }
}
