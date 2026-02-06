<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceInterval extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'service_name',
        'service_type',
        'interval_km',
        'next_due_km',
        'next_due_date',
        'description',
        'is_active',
    ];

    protected $casts = [
        'interval_km' => 'integer',
        'next_due_km' => 'integer',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the vehicle that owns the service interval.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get default service intervals based on vehicle type.
     */
    public static function getDefaultIntervals(string $tipeMotor): array
    {
        $intervals = [
            'matic' => [
                ['service_name' => 'Ganti Oli', 'service_type' => 'oil_change', 'interval_km' => 2000, 'description' => 'Ganti oli mesin matic'],
                ['service_name' => 'Cek Rem', 'service_type' => 'brake_check', 'interval_km' => 3000, 'description' => 'Pemeriksaan sistem rem'],
                ['service_name' => 'Ganti Oli CVT', 'service_type' => 'cvt_oil_change', 'interval_km' => 5000, 'description' => 'Ganti oli transmisi CVT'],
                ['service_name' => 'Servis Besar', 'service_type' => 'major_service', 'interval_km' => 10000, 'description' => 'Servis menyeluruh kendaraan'],
            ],
            'manual' => [
                ['service_name' => 'Ganti Oli', 'service_type' => 'oil_change', 'interval_km' => 2500, 'description' => 'Ganti oli mesin manual'],
                ['service_name' => 'Cek Rem', 'service_type' => 'brake_check', 'interval_km' => 3500, 'description' => 'Pemeriksaan sistem rem'],
                ['service_name' => 'Ganti Oli Gardan', 'service_type' => 'gear_oil_change', 'interval_km' => 6000, 'description' => 'Ganti oli gardan'],
                ['service_name' => 'Servis Besar', 'service_type' => 'major_service', 'interval_km' => 12000, 'description' => 'Servis menyeluruh kendaraan'],
            ],
            'sport' => [
                ['service_name' => 'Ganti Oli', 'service_type' => 'oil_change', 'interval_km' => 3000, 'description' => 'Ganti oli mesin sport'],
                ['service_name' => 'Cek Rem', 'service_type' => 'brake_check', 'interval_km' => 4000, 'description' => 'Pemeriksaan sistem rem racing'],
                ['service_name' => 'Tune Up Mesin', 'service_type' => 'engine_tune_up', 'interval_km' => 5000, 'description' => 'Penyetelan performa mesin'],
                ['service_name' => 'Ganti Oli Gardan', 'service_type' => 'gear_oil_change', 'interval_km' => 7000, 'description' => 'Ganti oli transmisi'],
                ['service_name' => 'Servis Besar', 'service_type' => 'major_service', 'interval_km' => 15000, 'description' => 'Servis menyeluruh kendaraan sport'],
            ],
        ];

        return $intervals[$tipeMotor] ?? $intervals['matic'];
    }
}
