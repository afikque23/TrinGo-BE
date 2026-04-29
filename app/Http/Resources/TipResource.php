<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;
        $deviceId = $request->header('X-Device-ID');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'tags' => $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'type' => $tag->type,
                    'color' => $tag->color,
                    'icon' => $tag->icon,
                ];
            }),
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $this->user->avatar
                    ? url('/api/v1/motorcycle/files/avatars/' . basename($this->user->avatar))
                    : null,
            ],
            'vehicle' => [
                'brand' => $this->vehicle_brand,
                'model' => $this->vehicle_model,
                'year' => $this->vehicle_year,
                'riding_style' => $this->riding_style,
            ],
            'estimated_time' => $this->estimated_time,
            'maintenance_interval' => [
                'distance_km' => $this->interval_distance_km,
                'time_months' => $this->interval_time_months,
            ],
            'stats' => [
                'rating' => (float) $this->rating,
                'likes_count' => $this->likes_count,
                'bookmarks_count' => $this->bookmarks_count,
                'shares_count' => $this->shares_count,
                'views_count' => $this->views_count,
                'ratings_count' => $this->ratings_count,
                'success_percentage' => $this->success_percentage,
            ],
            'hashtags' => $this->hashtags ?? [],
            'is_liked' => $this->isLikedBy($userId, $deviceId),
            'is_bookmarked' => $this->isBookmarkedBy($userId, $deviceId),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
