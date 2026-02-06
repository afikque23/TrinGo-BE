<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'service_type',
        'performed_at',
        'odometer',
        'cost_cents',
        'currency',
        'service_provider',
        'receipt_url',
        'notes',
    ];

    protected $casts = [
        'performed_at' => 'date',
        'odometer' => 'integer',
        'cost_cents' => 'integer',
    ];

    /**
     * Get the vehicle that owns the service history.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the documents for the service history.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the cost in currency format
     */
    public function getCostAttribute(): float
    {
        return $this->cost_cents / 100;
    }

    /**
     * Set the cost (automatically converts to cents)
     */
    public function setCostAttribute($value): void
    {
        $this->attributes['cost_cents'] = $value * 100;
    }
}
