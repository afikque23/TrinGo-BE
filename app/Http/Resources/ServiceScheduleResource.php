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
            'service_name' => $this->service_name ?? $this->serviceType->name,
            'schedule_type' => $this->schedule_type,
            'interval_value' => $this->interval_value,
            'last_service_mileage' => $this->last_service_mileage,
            'last_service_date' => $this->last_service_date?->format('Y-m-d'),
            'target_km' => $this->target_km,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'reminder_option' => $this->reminderOption ? [
                'id' => $this->reminderOption->id,
                'label' => $this->reminderOption->label,
                'value' => $this->reminderOption->value,
                'unit' => $this->reminderOption->unit,
            ] : null,
            'reminder_threshold' => $this->reminder_threshold,
            'reminder_sent' => $this->reminder_sent,
            'reminder_sent_at' => $this->reminder_sent_at?->toISOString(),
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
