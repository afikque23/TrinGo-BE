<?php

namespace App\Http\Requests;

use App\Models\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreServiceRequest extends FormRequest
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
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'odometer_km' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
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
            'vehicle_id.required' => 'Kendaraan harus dipilih.',
            'vehicle_id.exists' => 'Kendaraan tidak ditemukan.',
            'service_type_id.required' => 'Jenis service harus dipilih.',
            'service_type_id.exists' => 'Jenis service tidak ditemukan.',
            'service_date.required' => 'Tanggal service harus diisi.',
            'service_date.date' => 'Format tanggal service tidak valid.',
            'service_date.before_or_equal' => 'Tanggal service tidak boleh di masa depan.',
            'odometer_km.required' => 'Odometer harus diisi.',
            'odometer_km.integer' => 'Odometer harus berupa angka.',
            'odometer_km.min' => 'Odometer tidak boleh negatif.',
            'cost.required' => 'Biaya service harus diisi.',
            'cost.numeric' => 'Biaya harus berupa angka.',
            'cost.min' => 'Biaya tidak boleh negatif.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('service_type_id')) {
                $serviceType = ServiceType::find($this->service_type_id);
                
                if ($serviceType && !$serviceType->is_active) {
                    $validator->errors()->add(
                        'service_type_id',
                        'Jenis service yang dipilih tidak aktif. Silakan pilih jenis service lain.'
                    );
                }
            }
        });
    }
}
