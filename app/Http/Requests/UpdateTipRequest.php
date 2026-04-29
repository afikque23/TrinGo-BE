<?php

namespace App\Http\Requests;

use App\Models\Tip;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->exists('hashtags')) {
            return;
        }

        $hashtags = $this->input('hashtags');
        if (!is_array($hashtags)) {
            return;
        }

        $this->merge([
            'hashtags' => Tip::normalizeHashtags($hashtags),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:5', 'max:200'],
            'description' => ['sometimes', 'required', 'string', 'min:20', 'max:500'],
            
            // Vehicle info
            'vehicle.brand' => ['sometimes', 'required', 'string', 'max:50'],
            'vehicle.model' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'vehicle.year' => ['sometimes', 'required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'vehicle.riding_style' => ['sometimes', 'required', 'string', 'max:50'],
            
            // Estimated time
            'estimated_time' => ['nullable', 'string', 'max:50'],
            
            // Tools
            'tools' => ['sometimes', 'required', 'array', 'min:1'],
            'tools.*.name' => ['required', 'string', 'max:200'],
            'tools.*.is_optional' => ['boolean'],
            
            // Steps
            'steps' => ['sometimes', 'required', 'array', 'min:3'],
            'steps.*.title' => ['required', 'string', 'max:200'],
            'steps.*.description' => ['required', 'string'],
            
            // Maintenance interval
            'maintenance_interval.distance_km' => ['nullable', 'integer', 'min:100', 'max:50000'],
            'maintenance_interval.time_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            
            // Additional info
            'important_notes' => ['nullable', 'string'],
            'hashtags' => ['nullable', 'array', 'max:10'],
            'hashtags.*' => ['string', 'min:2', 'max:30'],
            'is_copyable' => ['boolean'],
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
            'title' => 'judul',
            'description' => 'deskripsi',
            'vehicle.brand' => 'merek motor',
            'vehicle.model' => 'model motor',
            'vehicle.year' => 'tahun motor',
            'vehicle.riding_style' => 'gaya berkendara',
            'estimated_time' => 'estimasi waktu',
            'tools' => 'alat',
            'steps' => 'langkah',
            'maintenance_interval.distance_km' => 'interval jarak',
            'maintenance_interval.time_months' => 'interval waktu',
            'important_notes' => 'catatan penting',
            'hashtags' => 'hashtag',
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
            'title.min' => 'Judul minimal 5 karakter.',
            'title.max' => 'Judul maksimal 200 karakter.',
            
            'description.min' => 'Deskripsi minimal 20 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            
            'vehicle.brand.required' => 'Merek motor harus dipilih.',
            'vehicle.model.required' => 'Model motor harus diisi.',
            'vehicle.year.required' => 'Tahun motor harus diisi.',
            'vehicle.riding_style.required' => 'Gaya berkendara harus dipilih.',
            
            'tools.required' => 'Minimal 1 alat harus diisi.',
            'tools.array' => 'Alat harus berupa daftar.',
            'tools.min' => 'Minimal 1 alat diperlukan.',
            
            'steps.min' => 'Minimal 3 langkah diperlukan.',
            
            'hashtags.max' => 'Hashtag maksimal 10 item.',
            'hashtags.*.min' => 'Panjang hashtag harus 2-30 karakter.',
            'hashtags.*.max' => 'Panjang hashtag harus 2-30 karakter.',
        ];
    }
}
