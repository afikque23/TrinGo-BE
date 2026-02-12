<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceScheduleResource extends JsonResource
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
                'id' => $this->vehicle->id,
                'name' => $this->vehicle->name,
                'brand' => $this->vehicle->brand,
                'model' => $this->vehicle->model,
                'current_km' => $this->vehicle->current_km,
            ],
            'service_type' => [
                'id' => $this->serviceType->id,
                'name' => $this->serviceType->name,
                'description' => $this->serviceType->description,
            ],
            'schedule_type' => $this->schedule_type,
            'target_km' => $this->target_km,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'reminder_option' => [
                'id' => $this->reminderOption->id,
                'label' => $this->reminderOption->label,
                'value' => $this->reminderOption->value,
                'unit' => $this->reminderOption->unit,
            ],
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
