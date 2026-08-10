<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
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
            'vehicle_id' => $this->vehicle_id,
            'vehicle' => [
                'id'    => $this->vehicle->id,
                'title' => $this->vehicle->title,
                'make'  => $this->vehicle->make,
                'model' => $this->vehicle->model,
            ],
            'started_by' => $this->started_by,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'distance_meters' => $this->distance_meters,
            'distance_km' => $this->distance_km,
            'start_odometer' => $this->start_odometer,
            'end_odometer' => $this->end_odometer,
            'avg_speed_kph' => $this->avg_speed_kph,
            'max_speed_kph' => $this->max_speed_kph,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
            'points_count' => $this->points->count(),
            'points' => TripPointResource::collection($this->whenLoaded('points')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // Suhu mesin DS18B20 — diambil dari rata-rata trip, atau last known temperature dari vehicle
            'engine_temp_c'   => $this->avg_temperature_c ?? $this->vehicle?->last_engine_temp_c,
            'max_engine_temp_c' => $this->max_temperature_c,
            'min_engine_temp_c' => $this->min_temperature_c,
            'engine_overheat' => $this->vehicle?->last_engine_overheat ?? false,
            'engine_temp_at'  => $this->vehicle?->last_engine_temp_at
                                    ? $this->vehicle->last_engine_temp_at->toIso8601String()
                                    : null,
        ];
    }
}