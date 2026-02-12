<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => 'Ganti Oli',
                'description' => 'Penggantian oli mesin dan filter oli',
                'is_active' => true,
            ],
            [
                'name' => 'Tune Up',
                'description' => 'Penyetelan mesin dan pembersihan komponen',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Ban',
                'description' => 'Penggantian ban depan atau belakang',
                'is_active' => true,
            ],
            [
                'name' => 'Service Berkala',
                'description' => 'Service rutin sesuai jadwal pabrik',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Kampas Rem',
                'description' => 'Penggantian kampas rem depan atau belakang',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Aki',
                'description' => 'Penggantian aki motor',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Rantai & Gear',
                'description' => 'Penggantian rantai dan gear (sproket)',
                'is_active' => true,
            ],
            [
                'name' => 'Cuci Motor',
                'description' => 'Pencucian motor dan pembersihan menyeluruh',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Busi',
                'description' => 'Penggantian busi motor',
                'is_active' => true,
            ],
            [
                'name' => 'Service Rem',
                'description' => 'Perbaikan dan penyetelan sistem pengereman',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Filter Udara',
                'description' => 'Penggantian atau pembersihan filter udara',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Kopling',
                'description' => 'Penggantian kampas kopling',
                'is_active' => true,
            ],
            [
                'name' => 'Perbaikan Kelistrikan',
                'description' => 'Perbaikan sistem kelistrikan motor',
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Lampu',
                'description' => 'Penggantian lampu depan, belakang, atau sein',
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'description' => 'Service atau perbaikan lainnya',
                'is_active' => true,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::create($serviceType);
        }

        $this->command->info('Service types seeded successfully!');
        $this->command->info('Created ' . count($serviceTypes) . ' service types');
    }
}
