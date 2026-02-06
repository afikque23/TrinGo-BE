<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceHistoryRequest extends FormRequest
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
            'service_type' => ['required', 'string', 'max:120'],
            'performed_at' => ['required', 'date', 'before_or_equal:today'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'service_provider' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // max 5MB
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
            'service_type.required' => 'Jenis servis wajib diisi.',
            'service_type.max' => 'Jenis servis maksimal 120 karakter.',
            'performed_at.required' => 'Tanggal servis wajib diisi.',
            'performed_at.date' => 'Tanggal servis harus berupa tanggal yang valid.',
            'performed_at.before_or_equal' => 'Tanggal servis tidak boleh di masa depan.',
            'odometer.integer' => 'Kilometer harus berupa angka.',
            'odometer.min' => 'Kilometer tidak boleh bernilai negatif.',
            'cost.numeric' => 'Biaya harus berupa angka.',
            'cost.min' => 'Biaya tidak boleh bernilai negatif.',
            'currency.size' => 'Kode mata uang harus 3 karakter (contoh: IDR, USD).',
            'service_provider.max' => 'Nama bengkel maksimal 150 karakter.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
            'receipt_photo.image' => 'File harus berupa gambar.',
            'receipt_photo.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp.',
            'receipt_photo.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
