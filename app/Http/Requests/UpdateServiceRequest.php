<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vehicle_id' => ['sometimes', 'integer', 'exists:vehicles,id'],
            'service_type_id' => ['sometimes', 'integer', 'exists:service_types,id'],
            'service_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'odometer_km' => ['sometimes', 'integer', 'min:0'],
            'cost' => ['sometimes', 'numeric', 'min:0'],
            'workshop_name' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt_image' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'service_type_id' => 'jenis service',
            'service_date' => 'tanggal service',
            'odometer_km' => 'odometer',
            'cost' => 'biaya',
            'workshop_name' => 'nama bengkel',
            'notes' => 'catatan',
            'receipt_image' => 'foto struk',
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
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan.',
            'service_type_id.exists' => 'Jenis service tidak ditemukan.',
            'service_date.date' => 'Format tanggal service tidak valid.',
            'service_date.before_or_equal' => 'Tanggal service tidak boleh di masa depan.',
            'odometer_km.integer' => 'Odometer harus berupa angka.',
            'odometer_km.min' => 'Odometer tidak boleh negatif.',
            'cost.numeric' => 'Biaya harus berupa angka.',
            'cost.min' => 'Biaya tidak boleh negatif.',
        ];
    }
}
