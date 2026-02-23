<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'vehicle_id',
        'category_key',
        'template_id',
        'title',
        'message',
        'data_payload',
        'priority',
        'sent_via',
        'push_sent',
        'push_success',
        'is_read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
        'data_payload' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vehicle related to the notification.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the template used for this notification.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Get the category of this notification.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NotificationCategory::class, 'category_key', 'key');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $categoryKey)
    {
        return $query->where('category_key', $categoryKey);
    }

    /**
     * Scope a query to order by latest.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Mark the notification as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        $this->is_read = true;
        $this->read_at = now();
        return $this->save();
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
}
