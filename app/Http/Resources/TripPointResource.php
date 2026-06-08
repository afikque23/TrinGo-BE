<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'sequence' => $this->sequence,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'altitude' => $this->altitude,
            'baro_rel_alt_m' => $this->baro_rel_alt_m,
            'grade_pct' => $this->grade_pct,
            'speed_kph' => $this->speed_kph,
            'accuracy_meters' => $this->accuracy_meters,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
        ];
    }
}
