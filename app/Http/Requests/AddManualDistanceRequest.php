<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddManualDistanceRequest extends FormRequest
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
            'distance_km' => 'required|numeric|min:0.01|max:9999',
            'trip_date' => 'required|date|before_or_equal:today',
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
            'vehicle_id.required' => 'Kendaraan harus dipilih',
            'vehicle_id.exists' => 'Kendaraan yang dipilih tidak ditemukan',
            'distance_km.required' => 'Jarak harus diisi',
            'distance_km.min' => 'Jarak minimal 0.01 km',
            'distance_km.max' => 'Jarak maksimal 9999 km',
            'trip_date.required' => 'Tanggal perjalanan harus diisi',
            'trip_date.before_or_equal' => 'Tanggal perjalanan tidak boleh di masa depan',
            'notes.max' => 'Catatan maksimal 1000 karakter',
        ];
    }
}
