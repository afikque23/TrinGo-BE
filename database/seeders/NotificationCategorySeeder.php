<?php

namespace Database\Seeders;

use App\Models\NotificationCategory;
use Illuminate\Database\Seeder;

class NotificationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Servis',
                'key' => 'service',
                'icon' => 'wrench',
                'color' => 'blue',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Perjalanan',
                'key' => 'trip',
                'icon' => 'map',
                'color' => 'green',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Peringatan',
                'key' => 'alert',
                'icon' => 'alert-triangle',
                'color' => 'red',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Rekomendasi',
                'key' => 'insight',
                'icon' => 'lightbulb',
                'color' => 'yellow',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            NotificationCategory::updateOrCreate(
                ['key' => $category['key']],
                $category
            );
        }
    }
}
