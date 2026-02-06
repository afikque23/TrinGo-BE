<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'title',
        'description',
        'reminder_type',
        'due_date',
        'due_odometer',
        'advance_days',
        'advance_km',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_odometer' => 'integer',
        'advance_days' => 'integer',
        'advance_km' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the vehicle that owns the reminder.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user that owns the reminder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if reminder is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->is_completed) {
            return false;
        }

        if ($this->due_date && $this->due_date->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Check if reminder should trigger notification
     */
    public function shouldNotify(?int $currentOdometer = null): bool
    {
        if ($this->is_completed) {
            return false;
        }

        // Check date-based reminder
        if ($this->due_date) {
            $notifyDate = $this->due_date->subDays($this->advance_days);
            if (now()->gte($notifyDate)) {
                return true;
            }
        }

        // Check odometer-based reminder
        if ($this->due_odometer && $currentOdometer && $this->advance_km) {
            $notifyOdometer = $this->due_odometer - $this->advance_km;
            if ($currentOdometer >= $notifyOdometer) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark reminder as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope for active reminders
     */
    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope for completed reminders
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for overdue reminders
     */
    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
                     ->where('due_date', '<', now());
    }
}
