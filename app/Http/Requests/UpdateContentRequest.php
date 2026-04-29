<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
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
        $contentId = $this->route('id');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('contents', 'slug')->ignore($contentId)],
            'type' => ['sometimes', 'required', 'string', Rule::in(['terms', 'privacy', 'guide', 'about', 'faq', 'system_info', 'support'])],
            'body' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['draft', 'published'])],
            'order' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul konten wajib diisi.',
            'title.max' => 'Judul konten maksimal 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan, silakan gunakan slug lain.',
            'type.required' => 'Tipe konten wajib diisi.',
            'type.in' => 'Tipe konten tidak valid. Pilihan: terms, privacy, guide, about, faq, system_info, support.',
            'body.required' => 'Isi konten wajib diisi.',
            'status.in' => 'Status tidak valid. Pilihan: draft, published.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan minimal 0.',
        ];
    }
}
