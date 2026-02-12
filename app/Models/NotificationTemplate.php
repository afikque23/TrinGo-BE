<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category_key',
        'trigger_type',
        'threshold_value',
        'priority',
        'channel',
        'message_template',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'threshold_value' => 'integer',
    ];

    /**
     * Get the category that owns the template.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NotificationCategory::class, 'category_key', 'key');
    }

    /**
     * Get the user who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted priority attribute.
     *
     * @return string
     */
    public function getFormattedPriorityAttribute(): string
    {
        $priorities = [
            'low' => 'Rendah',
            'normal' => 'Normal',
            'high' => 'Tinggi',
            'critical' => 'Kritikal',
        ];

        return $priorities[$this->priority] ?? ucfirst($this->priority);
    }

    /**
     * Get priority color for UI display.
     *
     * @return string
     */
    public function getPriorityColorAttribute(): string
    {
        $colors = [
            'low' => '#6A7282',
            'normal' => '#6A7282',
            'high' => '#FDC700',
            'critical' => '#FF6467',
        ];

        return $colors[$this->priority] ?? '#6A7282';
    }

    /**
     * Get formatted channel attribute.
     *
     * @return string
     */
    public function getFormattedChannelAttribute(): string
    {
        $channels = [
            'in_app' => 'In-App',
            'push' => 'Push',
            'email' => 'Email',
        ];

        return $channels[$this->channel] ?? ucfirst($this->channel);
    }
}
