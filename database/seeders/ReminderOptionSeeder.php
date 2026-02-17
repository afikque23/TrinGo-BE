<?php

namespace Database\Seeders;

use App\Models\ReminderOption;
use Illuminate\Database\Seeder;

class ReminderOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            // Jarak (Kilometer)
            ['label' => '100 km sebelum', 'value' => 100, 'unit' => 'km', 'is_active' => true],
            ['label' => '200 km sebelum', 'value' => 200, 'unit' => 'km', 'is_active' => true],
            ['label' => '300 km sebelum', 'value' => 300, 'unit' => 'km', 'is_active' => true],
            ['label' => '500 km sebelum', 'value' => 500, 'unit' => 'km', 'is_active' => true],
            ['label' => '1000 km sebelum', 'value' => 1000, 'unit' => 'km', 'is_active' => true],
            
            // Waktu - Hari
            ['label' => '3 hari sebelum', 'value' => 3, 'unit' => 'days', 'is_active' => true],
            ['label' => '7 hari sebelum', 'value' => 7, 'unit' => 'days', 'is_active' => true],
            ['label' => '14 hari sebelum', 'value' => 14, 'unit' => 'days', 'is_active' => true],
            
            // Waktu - Minggu
            ['label' => '1 minggu sebelum', 'value' => 1, 'unit' => 'weeks', 'is_active' => true],
            ['label' => '2 minggu sebelum', 'value' => 2, 'unit' => 'weeks', 'is_active' => true],
            
            // Waktu - Bulan
            ['label' => '1 bulan sebelum', 'value' => 1, 'unit' => 'months', 'is_active' => true],
            ['label' => '2 bulan sebelum', 'value' => 2, 'unit' => 'months', 'is_active' => true],
            ['label' => '3 bulan sebelum', 'value' => 3, 'unit' => 'months', 'is_active' => true],
            ['label' => '6 bulan sebelum', 'value' => 6, 'unit' => 'months', 'is_active' => true],
            
            // Waktu - Tahun
            ['label' => '1 tahun sebelum', 'value' => 1, 'unit' => 'years', 'is_active' => true],
        ];

        foreach ($options as $option) {
            ReminderOption::firstOrCreate(
                ['value' => $option['value'], 'unit' => $option['unit']],
                $option
            );
        }
    }
}
