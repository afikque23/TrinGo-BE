<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TipShareRequest extends FormRequest
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
            'platform' => ['required', 'string', 'in:whatsapp,telegram,facebook,twitter,copy_link,other'],
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
            'platform.required' => 'Platform harus diisi.',
            'platform.in' => 'Platform tidak valid.',
        ];
    }
}
