<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth:sanctum middleware
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
            'service_type_id' => 'required|integer|exists:service_types,id',
            'service_name' => 'nullable|string|max:200',
            'schedule_type' => 'required|in:km,time',
            'target_km' => [
                'nullable',
                'integer',
                'min:1',
                Rule::requiredIf(function () {
                    return $this->schedule_type === 'km';
                }),
            ],
            'target_date' => [
                'nullable',
                'date',
                'after:today',
                Rule::requiredIf(function () {
                    return $this->schedule_type === 'time';
                }),
            ],
            'reminder_option_id' => [
                'nullable',
                'integer',
                Rule::exists('reminder_options', 'id')->where('is_active', true),
            ],
            'notes' => 'nullable|string|max:1000',
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
            'vehicle_id.required' => 'Vehicle is required.',
            'vehicle_id.exists' => 'Selected vehicle not found or does not belong to you.',
            'service_type_id.required' => 'Service type is required.',
            'service_type_id.exists' => 'Selected service type not found.',
            'schedule_type.required' => 'Schedule type is required.',
            'schedule_type.in' => 'Schedule type must be either km or time.',
            'target_km.required' => 'Target kilometers is required for km-based schedule.',
            'target_km.min' => 'Target kilometers must be at least 1.',
            'target_date.required' => 'Target date is required for time-based schedule.',
            'target_date.after' => 'Target date must be in the future.',
            'reminder_option_id.exists' => 'Selected reminder option not found or inactive.',
        ];
    }
}
