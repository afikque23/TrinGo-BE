<?php

namespace Database\Seeders;

use App\Models\TipTag;
use Illuminate\Database\Seeder;

class TipTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Riding style tags
            [
                'name' => 'Harian / Commuter',
                'type' => 'riding_style',
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ],
            [
                'name' => 'Touring',
                'type' => 'riding_style',
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ],
            [
                'name' => 'Sport / Track',
                'type' => 'riding_style',
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ],
            [
                'name' => 'Off-road',
                'type' => 'riding_style',
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ],
            [
                'name' => 'Urban / City',
                'type' => 'riding_style',
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ],
            [
                'name' => 'Kombinasi',
                'type' => 'riding_style',
                'color' => '#2B7FFF',
                'icon' => 'directions_bike',
            ],
            
            // Special tags
            [
                'name' => 'Trending',
                'type' => 'trending',
                'color' => '#FF8904',
                'icon' => 'trending_up',
            ],
            [
                'name' => 'Rekomendasi AI',
                'type' => 'ai_recommended',
                'color' => '#6B7C4F',
                'icon' => 'smart_toy',
            ],
            [
                'name' => 'Terbukti',
                'type' => 'verified',
                'color' => '#51A2FF',
                'icon' => 'verified',
            ],
        ];

        foreach ($tags as $tag) {
            TipTag::firstOrCreate(
                [
                    'name' => $tag['name'],
                    'type' => $tag['type'],
                ],
                [
                    'color' => $tag['color'],
                    'icon' => $tag['icon'],
                ]
            );
        }
    }
}
