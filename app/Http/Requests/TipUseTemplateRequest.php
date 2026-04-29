<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipUseTemplateRequest extends FormRequest
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
            'schedule_type' => ['required', 'string', 'in:interval,one_time'],
            'interval_type' => ['required_if:schedule_type,interval', 'string', 'in:distance,time,both'],
            'interval_value' => ['required_if:schedule_type,interval', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'schedule_type' => 'tipe jadwal',
            'interval_type' => 'tipe interval',
            'interval_value' => 'nilai interval',
            'start_date' => 'tanggal mulai',
            'notes' => 'catatan',
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
            'schedule_type.required' => 'Tipe jadwal harus dipilih.',
            'schedule_type.in' => 'Tipe jadwal tidak valid.',
            'interval_type.required_if' => 'Tipe interval harus diisi untuk jadwal interval.',
            'interval_type.in' => 'Tipe interval tidak valid.',
            'interval_value.required_if' => 'Nilai interval harus diisi untuk jadwal interval.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
        ];
    }
}
