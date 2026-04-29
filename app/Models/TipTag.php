<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'color',
        'icon',
    ];

    /**
     * Get the tips that have this tag.
     */
    public function tips(): BelongsToMany
    {
        return $this->belongsToMany(Tip::class, 'tip_tag_pivot', 'tip_tag_id', 'tip_id')
            ->withTimestamps();
    }
}
