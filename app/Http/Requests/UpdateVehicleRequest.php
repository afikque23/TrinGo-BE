<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Vehicle;

class UpdateVehicleRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $vehicleParam = $this->route('vehicle');
        $vehicleId = $vehicleParam instanceof Vehicle ? $vehicleParam->getKey() : $vehicleParam;
        
        return [
            // device_id boleh dipakai lebih dari 1 motor dalam akun yang SAMA,
            // tetapi tidak boleh dipakai akun LAIN.
            'device_id' => [
                'nullable',
                'string',
                'max:128',
                Rule::unique('vehicles', 'device_id')
                    ->where(fn ($query) => $query->where('user_id', '!=', auth()->id())),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'tipe_motor' => ['sometimes', 'required', 'in:matic,manual,sport,adventure'],
            'vin' => ['nullable', 'string', 'max:64', Rule::unique('vehicles')->ignore($vehicleId)],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // Max 5MB
            // Parameter default untuk kalkulasi jadwal servis
            'default_beban'           => ['nullable', 'in:ringan,sedang,berat'],
            'default_penumpang'       => ['nullable', 'boolean'],
            'default_gaya_berkendara' => ['nullable', 'in:pelan,normal,agresif'],
            'default_kondisi_jalan'   => ['nullable', 'in:macet,sedang,lancar'],
            'default_medan'           => ['nullable', 'in:datar,berbukit,campuran'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'device_id.unique' => 'Device ID sudah terpakai oleh akun lain',
            'title.required' => 'Nama kendaraan wajib diisi',
            'title.max' => 'Nama kendaraan maksimal 200 karakter',
            'tipe_motor.required' => 'Tipe motor wajib dipilih',
            'tipe_motor.in' => 'Tipe motor harus salah satu dari: matic, manual, atau sport',
            'year.integer' => 'Tahun harus berupa angka',
            'year.min' => 'Tahun minimal 1900',
            'year.max' => 'Tahun tidak valid',
            'vin.unique' => 'VIN sudah terdaftar untuk kendaraan lain',
            'odometer.integer' => 'Odometer harus berupa angka',
            'odometer.min'                    => 'Odometer tidak boleh negatif',
            'photo.image'                     => 'File harus berupa gambar',
            'photo.mimes'                     => 'Format gambar harus jpeg, jpg, png, atau webp',
            'photo.max'                       => 'Ukuran gambar maksimal 5MB',
            'kapasitas_cc.in'                 => 'Kapasitas CC harus salah satu: <125, 125-250, atau >250',
            'transmisi.in'                    => 'Transmisi harus manual atau cvt',
        ];
    }
}
