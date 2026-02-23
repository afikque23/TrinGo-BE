<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Hitung total vehicles
        $totalVehicles = $this->vehicles()->count();

        // Generate avatar URL using file serving route
        $avatarUrl = null;
        if ($this->avatar) {
            $filename = basename($this->avatar);
            $avatarUrl = url('/api/v1/motorcycle/files/avatars/' . $filename);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            'avatar' => $avatarUrl,
            'role' => $this->role ?? 'user',
            'is_active' => $this->is_active ?? true,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Stats
            'stats' => [
                'total_vehicles' => $totalVehicles,
            ],
        ];
    }
}
