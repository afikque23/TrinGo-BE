<?php

namespace Database\Seeders;

use App\Models\ComponentConfig;
use App\Models\FuzzyRule;
use App\Models\FuzzyVariable;
use App\Models\MotorType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateFuzzyFromReportSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'matic'  => MotorType::firstOrCreate(['slug' => 'matic'], ['name' => 'Matic', 'is_active' => true]),
            'manual' => MotorType::firstOrCreate(['slug' => 'manual'], ['name' => 'Manual/Bebek', 'is_active' => true]),
            'sport'  => MotorType::firstOrCreate(['slug' => 'sport'], ['name' => 'Sport', 'is_active' => true]),
        ];

        // ---------------------------------------------------------------------
        // 1. DATA MOTOR MATIC (laporan_konfigurasi_fuzzy.md)
        // ---------------------------------------------------------------------
        $maticComponents = [
            [
                'name' => 'Oli Mesin',
                'warn' => 2500,
                'critical' => 4000,
                'reset_interval' => 120,
                'active_vars' => ['jarak', 'durasi', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 1500], 'medium' => [1000, 2500, 4000], 'high' => [2500, 4000, 9999]],
                    'durasi' => ['low' => [0, 0, 60], 'medium' => [45, 90, 120], 'high' => [90, 120, 999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],

                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Oli Gardan',
                'warn' => 6000,
                'critical' => 8000,
                'reset_interval' => 730,
                'active_vars' => ['jarak', 'durasi', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 3500], 'medium' => [2500, 6000, 8000], 'high' => [6000, 8000, 9999]],
                    'durasi' => ['low' => [0, 0, 300], 'medium' => [250, 540, 730], 'high' => [540, 730, 999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],

                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Filter Udara',
                'warn' => 12000,
                'critical' => 16000,
                'reset_interval' => 180,
                'active_vars' => ['jarak', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 7000], 'medium' => [5000, 12000, 16000], 'high' => [12000, 16000, 99999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Baik', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Busi',
                'warn' => 6000,
                'critical' => 8000,
                'reset_interval' => 240,
                'active_vars' => ['jarak', 'durasi'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 3500], 'medium' => [2500, 6000, 8000], 'high' => [6000, 8000, 9999]],
                    'durasi' => ['low' => [0, 0, 100], 'medium' => [80, 180, 240], 'high' => [180, 240, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'CVT/Belt',
                'warn' => 20000,
                'critical' => 24000,
                'reset_interval' => 720,
                'active_vars' => ['jarak', 'durasi'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 12000], 'medium' => [8000, 20000, 24000], 'high' => [20000, 24000, 99999]],
                    'durasi' => ['low' => [0, 0, 300], 'medium' => [250, 540, 720], 'high' => [540, 720, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Roller CVT',
                'warn' => 20000,
                'critical' => 24000,
                'reset_interval' => 720,
                'active_vars' => ['jarak', 'durasi'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 12000], 'medium' => [8000, 20000, 24000], 'high' => [20000, 24000, 99999]],
                    'durasi' => ['low' => [0, 0, 300], 'medium' => [250, 540, 720], 'high' => [540, 720, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Rem',
                'warn' => 8000,
                'critical' => 12000,
                'reset_interval' => 365,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 5000], 'medium' => [3000, 8000, 12000], 'high' => [8000, 12000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Ban',
                'warn' => 12000,
                'critical' => 15000,
                'reset_interval' => 365,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 7000], 'medium' => [5000, 12000, 15000], 'high' => [12000, 15000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Aki',
                'warn' => 540,
                'critical' => 730,
                'reset_interval' => 730,
                'active_vars' => ['durasi'],
                'mfs' => [
                    'durasi' => ['low' => [0, 0, 300], 'medium' => [250, 540, 730], 'high' => [540, 730, 999]],
                ],
                'rules' => [
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'durasi', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'durasi', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Cairan Pendingin',
                'warn' => 24000,
                'critical' => 36000,
                'reset_interval' => 1095,
                'active_vars' => ['jarak', 'durasi'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 14000], 'medium' => [10000, 24000, 36000], 'high' => [24000, 36000, 99999]],
                    'durasi' => ['low' => [0, 0, 400], 'medium' => [300, 730, 1095], 'high' => [730, 1095, 9999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
        ];

        // ---------------------------------------------------------------------
        // 2. DATA MOTOR BEBEK / MANUAL (laporan_konfigurasi_fuzzy_bebek_manual.md)
        // ---------------------------------------------------------------------
        $manualComponents = [
            [
                'name' => 'Oli Mesin',
                'warn' => 2500,
                'critical' => 4000,
                'reset_interval' => 120,
                'active_vars' => ['jarak', 'durasi', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 1500], 'medium' => [1000, 2500, 4000], 'high' => [2500, 4000, 9999]],
                    'durasi' => ['low' => [0, 0, 60], 'medium' => [45, 90, 120], 'high' => [90, 120, 999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],

                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Filter Udara',
                'warn' => 12000,
                'critical' => 16000,
                'reset_interval' => 180,
                'active_vars' => ['jarak', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 7000], 'medium' => [5000, 12000, 16000], 'high' => [12000, 16000, 99999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Baik', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Rantai Roda',
                'warn' => 15000,
                'critical' => 20000,
                'reset_interval' => 365,
                'active_vars' => ['jarak', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 8000], 'medium' => [5000, 15000, 20000], 'high' => [15000, 20000, 99999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Baik', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Busi',
                'warn' => 6000,
                'critical' => 8000,
                'reset_interval' => 240,
                'active_vars' => ['jarak', 'durasi'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 3500], 'medium' => [2500, 6000, 8000], 'high' => [6000, 8000, 9999]],
                    'durasi' => ['low' => [0, 0, 100], 'medium' => [80, 180, 240], 'high' => [180, 240, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Kampas Kopling',
                'warn' => 20000,
                'critical' => 24000,
                'reset_interval' => 540,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 12000], 'medium' => [8000, 20000, 24000], 'high' => [20000, 24000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Rem',
                'warn' => 8000,
                'critical' => 12000,
                'reset_interval' => 365,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 5000], 'medium' => [3000, 8000, 12000], 'high' => [8000, 12000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Ban',
                'warn' => 12000,
                'critical' => 15000,
                'reset_interval' => 365,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 7000], 'medium' => [5000, 12000, 15000], 'high' => [12000, 15000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Aki',
                'warn' => 540,
                'critical' => 730,
                'reset_interval' => 730,
                'active_vars' => ['durasi'],
                'mfs' => [
                    'durasi' => ['low' => [0, 0, 300], 'medium' => [250, 540, 730], 'high' => [540, 730, 999]],
                ],
                'rules' => [
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'durasi', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'durasi', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
        ];

        // ---------------------------------------------------------------------
        // 3. DATA MOTOR SPORT / KOPLING (laporan_konfigurasi_fuzzy_sport_kopling.md)
        // ---------------------------------------------------------------------
        $sportComponents = [
            [
                'name' => 'Oli Mesin',
                'warn' => 4000,
                'critical' => 6000,
                'reset_interval' => 180,
                'active_vars' => ['jarak', 'durasi', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 2500], 'medium' => [2000, 4000, 6000], 'high' => [4000, 6000, 9999]],
                    'durasi' => ['low' => [0, 0, 90], 'medium' => [60, 120, 180], 'high' => [120, 180, 999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],

                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Filter Udara',
                'warn' => 14000,
                'critical' => 18000,
                'reset_interval' => 180,
                'active_vars' => ['jarak', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 8000], 'medium' => [6000, 14000, 18000], 'high' => [14000, 18000, 99999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Baik', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Rantai Roda',
                'warn' => 15000,
                'critical' => 20000,
                'reset_interval' => 365,
                'active_vars' => ['jarak', 'intensitas'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 8000], 'medium' => [5000, 15000, 20000], 'high' => [15000, 20000, 99999]],
                    'intensitas' => ['low' => [0, 0, 20], 'medium' => [15, 30, 50], 'high' => [40, 60, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'high', 'output' => 'Perlu Servis', 'weight' => 0.7],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'medium', 'output' => 'Baik', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'intensitas', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Busi',
                'warn' => 9000,
                'critical' => 12000,
                'reset_interval' => 360,
                'active_vars' => ['jarak', 'durasi'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 5000], 'medium' => [4000, 9000, 12000], 'high' => [9000, 12000, 99999]],
                    'durasi' => ['low' => [0, 0, 180], 'medium' => [120, 270, 360], 'high' => [270, 360, 999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.9],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 0.8],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Kampas Kopling',
                'warn' => 20000,
                'critical' => 24000,
                'reset_interval' => 540,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 12000], 'medium' => [8000, 20000, 24000], 'high' => [20000, 24000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Rem',
                'warn' => 12000,
                'critical' => 15000,
                'reset_interval' => 365,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 7000], 'medium' => [5000, 12000, 15000], 'high' => [12000, 15000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Ban',
                'warn' => 12000,
                'critical' => 15000,
                'reset_interval' => 365,
                'active_vars' => ['jarak'],
                'mfs' => [
                    'jarak' => ['low' => [0, 0, 7000], 'medium' => [5000, 12000, 15000], 'high' => [12000, 15000, 99999]],
                ],
                'rules' => [
                    ['var1' => 'jarak', 'label1' => 'high', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'jarak', 'label1' => 'low', 'op' => 'AND', 'var2' => 'jarak', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
            [
                'name' => 'Aki',
                'warn' => 540,
                'critical' => 730,
                'reset_interval' => 730,
                'active_vars' => ['durasi'],
                'mfs' => [
                    'durasi' => ['low' => [0, 0, 300], 'medium' => [250, 540, 730], 'high' => [540, 730, 999]],
                ],
                'rules' => [
                    ['var1' => 'durasi', 'label1' => 'high', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'high', 'output' => 'Kritis', 'weight' => 1.0],
                    ['var1' => 'durasi', 'label1' => 'medium', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'medium', 'output' => 'Perlu Servis', 'weight' => 1.0],
                    ['var1' => 'durasi', 'label1' => 'low', 'op' => 'AND', 'var2' => 'durasi', 'label2' => 'low', 'output' => 'Baik', 'weight' => 1.0],
                ]
            ],
        ];

        $allData = [
            'matic'  => $maticComponents,
            'manual' => $manualComponents,
            'sport'  => $sportComponents,
        ];

        foreach ($allData as $slug => $componentsData) {
            $motorType = $types[$slug];

            foreach ($componentsData as $c) {
                $component = ComponentConfig::updateOrCreate(
                    ['motor_type_id' => $motorType->id, 'name' => $c['name']],
                    [
                        'warn'           => $c['warn'],
                        'critical'       => $c['critical'],
                        'reset_interval' => $c['reset_interval'],
                        'active_vars'    => $c['active_vars'],
                        'is_active'      => true,
                        'is_custom'      => false,
                    ]
                );

                // Update Fuzzy Variables Membership Functions
                foreach ($c['mfs'] as $varKey => $mf) {
                    FuzzyVariable::updateOrCreate(
                        ['component_config_id' => $component->id, 'var_key' => $varKey],
                        [
                            'low_a'  => $mf['low'][0],    'low_b'  => $mf['low'][1],    'low_c'  => $mf['low'][2],
                            'med_a'  => $mf['medium'][0], 'med_b'  => $mf['medium'][1], 'med_c'  => $mf['medium'][2],
                            'high_a' => $mf['high'][0],   'high_b' => $mf['high'][1],   'high_c' => $mf['high'][2],
                        ]
                    );
                }

                // Update Fuzzy Rules
                FuzzyRule::where('component_config_id', $component->id)->delete();

                foreach ($c['rules'] as $r) {
                    FuzzyRule::create([
                        'component_config_id' => $component->id,
                        'var1'     => $r['var1'],
                        'label1'   => $r['label1'],
                        'operator' => $r['op'],
                        'var2'     => $r['var2'],
                        'label2'   => $r['label2'],
                        'output'   => $r['output'],
                        'weight'   => $r['weight'],
                    ]);
                }
            }
        }

        // Clean AI recommendation cache to force recalculation with new fuzzy configs
        DB::table('ai_recommendation_caches')->truncate();

        $this->command->info('✓ Konfigurasi Fuzzy untuk Motor MATIC, BEBEK/MANUAL, & SPORT/KOPLING berhasil diimpor ke database!');
    }
}
