<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'template_id',
        'category_key',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the user that owns the notification preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Get the notification category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NotificationCategory::class, 'category_key', 'key');
    }

    /**
     * Check if notification is enabled for a user and template.
     * 
     * Priority:
     * 1. Check template-specific preference
     * 2. Check category-level preference
     * 3. Default to enabled if no preference exists
     *
     * @param User $user
     * @param NotificationTemplate $template
     * @return bool
     */
    public static function isEnabledFor(User $user, NotificationTemplate $template): bool
    {
        // First, check if there's a template-specific preference
        $templatePreference = self::where('user_id', $user->id)
            ->where('template_id', $template->id)
            ->first();

        if ($templatePreference) {
            return $templatePreference->is_enabled;
        }

        // If no template preference, check category-level preference
        $categoryPreference = self::where('user_id', $user->id)
            ->where('category_key', $template->category_key)
            ->whereNull('template_id')
            ->first();

        if ($categoryPreference) {
            return $categoryPreference->is_enabled;
        }

        // Default to enabled if no preference is set
        return true;
    }
}
