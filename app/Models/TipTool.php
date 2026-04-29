<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'tip_id',
        'name',
        'is_optional',
        'order',
    ];

    protected $casts = [
        'is_optional' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the tip that owns the tool.
     */
    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }
}
