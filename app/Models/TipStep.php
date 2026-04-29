<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'tip_id',
        'step_number',
        'title',
        'description',
        'image',
    ];

    protected $casts = [
        'step_number' => 'integer',
    ];

    /**
     * Get the tip that owns the step.
     */
    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }
}
