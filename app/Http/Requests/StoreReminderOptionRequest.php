<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReminderOptionRequest extends FormRequest
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
            'label' => [
                'required',
                'string',
                'max:100'
            ],
            'value' => [
                'required',
                'integer',
                'min:1',
                'max:999999'
            ],
            'unit' => [
                'required',
                Rule::in(['km', 'days'])
            ],
            'is_active' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'label.required' => 'Label pengingat wajib diisi.',
            'label.string' => 'Label pengingat harus berupa teks.',
            'label.max' => 'Label pengingat maksimal 100 karakter.',
            'value.required' => 'Nilai pengingat wajib diisi.',
            'value.integer' => 'Nilai pengingat harus berupa angka.',
            'value.min' => 'Nilai pengingat minimal 1.',
            'value.max' => 'Nilai pengingat maksimal 999999.',
            'unit.required' => 'Unit pengingat wajib diisi.',
            'unit.in' => 'Unit pengingat harus km atau days.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default is_active jika tidak dikirim
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }
}
