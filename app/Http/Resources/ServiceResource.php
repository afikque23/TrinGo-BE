<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
                'title' => $this->vehicle->title,
            ],
            'service_type_id' => $this->service_type_id,
            'service_type' => [
                'id' => $this->serviceType->id,
                'name' => $this->serviceType->name,
                'description' => $this->serviceType->description,
            ],
            'service_date' => $this->service_date->format('Y-m-d'),
            'odometer_km' => $this->odometer_km,
            'cost' => (float) $this->cost,
            'workshop_name' => $this->workshop_name,
            'notes' => $this->notes,
            'receipt_image' => $this->receipt_image,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
