<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'total_distance' => 'required|numeric|min:0',
            'duration' => 'nullable|integer|min:0',
            'average_speed' => 'nullable|numeric|min:0',
            'max_speed' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:active,completed,paused',
            'notes' => 'nullable|string|max:1000',
            'points' => 'required|array|min:1',
            'points.*.latitude' => 'required|numeric|between:-90,90',
            'points.*.longitude' => 'required|numeric|between:-180,180',
            'points.*.speed' => 'nullable|numeric|min:0',
            'points.*.altitude' => 'nullable|numeric',
            'points.*.accuracy' => 'nullable|numeric|min:0',
            'points.*.timestamp' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Vehicle ID is required',
            'vehicle_id.exists' => 'The selected vehicle does not exist',
            'start_time.required' => 'Start time is required',
            'end_time.after' => 'End time must be after start time',
            'total_distance.required' => 'Total distance is required',
            'total_distance.min' => 'Total distance cannot be negative',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be active, completed, or paused',
            'points.required' => 'Trip points are required',
            'points.min' => 'At least one trip point is required',
            'points.*.latitude.required' => 'Latitude is required for all points',
            'points.*.latitude.between' => 'Latitude must be between -90 and 90',
            'points.*.longitude.required' => 'Longitude is required for all points',
            'points.*.longitude.between' => 'Longitude must be between -180 and 180',
            'points.*.timestamp.required' => 'Timestamp is required for all points',
        ];
    }
}
