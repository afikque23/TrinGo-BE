<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_year',
        'riding_style',
        'estimated_time',
        'interval_distance_km',
        'interval_time_months',
        'important_notes',
        'hashtags',
        'is_copyable',
        'status',
        'rating',
        'ratings_count',
    ];

    protected $casts = [
        'vehicle_year' => 'integer',
        'interval_distance_km' => 'integer',
        'interval_time_months' => 'integer',
        'hashtags' => 'array',
        'is_copyable' => 'boolean',
        'rating' => 'decimal:2',
        'likes_count' => 'integer',
        'bookmarks_count' => 'integer',
        'shares_count' => 'integer',
            'ratings_count' => 'integer',
        'views_count' => 'integer',
        'usage_count' => 'integer',
        'success_count' => 'integer',
    ];

    protected $appends = [
        'success_percentage',
    ];

    /**
     * Normalize hashtags before persisting.
     */
    public function setHashtagsAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['hashtags'] = null;
            return;
        }

        $hashtags = is_array($value) ? $value : [$value];

        $this->attributes['hashtags'] = json_encode(
            static::normalizeHashtags($hashtags),
            JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Get the user that owns the tip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tags for the tip.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TipTag::class, 'tip_tag_pivot', 'tip_id', 'tip_tag_id')
            ->withTimestamps();
    }

    /**
     * Get the tools for the tip.
     */
    public function tools(): HasMany
    {
        return $this->hasMany(TipTool::class)->orderBy('order');
    }

    /**
     * Get the steps for the tip.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(TipStep::class)->orderBy('step_number');
    }

    /**
     * Get the likes for the tip.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(TipLike::class);
    }

    /**
     * Get the bookmarks for the tip.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(TipBookmark::class);
    }

    /**
     * Get the shares for the tip.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(TipShare::class);
    }

    /**
     * Check if tip is liked by user or device.
     */
    public function isLikedBy($userId, $deviceId = null): bool
    {
        $query = $this->likes();
        
        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($deviceId) {
            $query->where('device_id', $deviceId);
        } else {
            return false;
        }
        
        return $query->exists();
    }

    /**
     * Check if tip is bookmarked by user or device.
     */
    public function isBookmarkedBy($userId, $deviceId = null): bool
    {
        $query = $this->bookmarks();
        
        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($deviceId) {
            $query->where('device_id', $deviceId);
        } else {
            return false;
        }
        
        return $query->exists();
    }

    /**
     * Get success percentage.
     */
    public function getSuccessPercentageAttribute(): int
    {
        if ($this->usage_count == 0) {
            return 0;
        }
        
        return round(($this->success_count / $this->usage_count) * 100);
    }

    /**
     * Get maintenance interval description.
     */
    public function getMaintenanceIntervalDescriptionAttribute(): ?string
    {
        if (!$this->interval_distance_km && !$this->interval_time_months) {
            return null;
        }
        
        $parts = [];
        
        if ($this->interval_distance_km) {
            $parts[] = "{$this->interval_distance_km} km";
        }
        
        if ($this->interval_time_months) {
            $parts[] = "{$this->interval_time_months} bulan";
        }
        
        return "Lakukan setiap " . implode(' atau ', $parts);
    }

    /**
     * Scope for published tips only.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for filtering by brand.
     */
    public function scopeByBrand($query, $brands)
    {
        if (empty($brands)) {
            return $query;
        }
        
        $brandsArray = is_array($brands) ? $brands : explode(',', $brands);
        return $query->whereIn('vehicle_brand', $brandsArray);
    }

    /**
     * Scope for filtering by riding style.
     */
    public function scopeByRidingStyle($query, $ridingStyles)
    {
        if (empty($ridingStyles)) {
            return $query;
        }
        
        $ridingStylesArray = is_array($ridingStyles) ? $ridingStyles : explode(',', $ridingStyles);
        return $query->whereIn('riding_style', $ridingStylesArray);
    }

    /**
     * Scope for filtering by tags.
     */
    public function scopeByTags($query, $tags)
    {
        if (empty($tags)) {
            return $query;
        }
        
        $tagsArray = is_array($tags) ? $tags : explode(',', $tags);
        
        return $query->whereHas('tags', function ($q) use ($tagsArray) {
            $q->whereIn('name', $tagsArray);
        });
    }

    /**
     * Scope for filtering by hashtags.
     */
    public function scopeByHashtags($query, $hashtags)
    {
        if (empty($hashtags)) {
            return $query;
        }

        $hashtagsArray = is_array($hashtags) ? $hashtags : explode(',', $hashtags);
        $normalizedHashtags = static::normalizeHashtags($hashtagsArray);

        if (empty($normalizedHashtags)) {
            return $query;
        }

        return $query->where(function ($q) use ($normalizedHashtags) {
            foreach ($normalizedHashtags as $index => $hashtag) {
                $encodedHashtag = json_encode(mb_strtolower($hashtag), JSON_UNESCAPED_UNICODE);

                if ($index === 0) {
                    $q->whereRaw('LOWER(CAST(hashtags AS CHAR)) LIKE ?', ['%' . $encodedHashtag . '%']);
                    continue;
                }

                $q->orWhereRaw('LOWER(CAST(hashtags AS CHAR)) LIKE ?', ['%' . $encodedHashtag . '%']);
            }
        });
    }

    /**
     * Scope for searching.
     */
    public function scopeSearch($query, $search)
    {
        $keyword = static::normalizeKeyword($search);

        if ($keyword === '') {
            return $query;
        }

        $keywordLike = '%' . mb_strtolower($keyword) . '%';
        
        return $query->where(function ($q) use ($keywordLike) {
            $q->whereRaw('LOWER(title) LIKE ?', [$keywordLike])
              ->orWhereRaw('LOWER(description) LIKE ?', [$keywordLike])
              ->orWhereRaw('LOWER(CAST(hashtags AS CHAR)) LIKE ?', [$keywordLike]);
        });
    }

    /**
     * Normalize hashtag items according to API contract.
     *
     * @param array<int, mixed> $hashtags
     * @return array<int, string>
     */
    public static function normalizeHashtags(array $hashtags): array
    {
        $result = [];
        $seen = [];

        foreach ($hashtags as $hashtag) {
            if (!is_scalar($hashtag)) {
                continue;
            }

            $normalized = static::normalizeKeyword((string) $hashtag);

            if ($normalized === '') {
                continue;
            }

            $key = mb_strtolower($normalized);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $result[] = $normalized;
        }

        return $result;
    }

    /**
     * Normalize a keyword/hashtag term.
     */
    public static function normalizeKeyword(?string $keyword): string
    {
        if ($keyword === null) {
            return '';
        }

        $normalized = trim($keyword);
        $normalized = preg_replace('/^#+/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    /**
     * Scope for sorting.
     */
    public function scopeSortBy($query, $sortBy)
    {
        switch ($sortBy) {
            case 'popular':
                return $query->orderBy('views_count', 'desc')
                             ->orderBy('likes_count', 'desc');
            
            case 'rating':
                return $query->orderBy('rating', 'desc')
                             ->orderBy('likes_count', 'desc');
            
            case 'relevance':
                // For now, same as latest. Can be enhanced with search relevance
                return $query->latest();
            
            case 'latest':
            default:
                return $query->latest();
        }
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Increment success count.
     */
    public function incrementSuccess(): void
    {
        $this->increment('success_count');
    }

    /**
     * Get the ratings for the tip.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(TipRating::class);
    }

    /**
     * Update average rating based on all ratings.
     */
    public function updateAverageRating(): void
    {
        $avgRating = TipRating::where('tip_id', $this->id)->avg('rating');
        $ratingCount = TipRating::where('tip_id', $this->id)->count();

        $this->update([
            'rating' => $avgRating ? round($avgRating, 2) : 0,
            'ratings_count' => $ratingCount,
        ]);
    }
}
