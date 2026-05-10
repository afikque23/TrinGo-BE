<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call all seeders
        $this->call([
            AdminSeeder::class,
            ContentSeeder::class,
            ServiceTypeSeeder::class,
            ReminderOptionSeeder::class,
            NotificationCategorySeeder::class,
            NotificationTemplateSeeder::class,
            FuzzyLogicSeeder::class,
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        $this->command->info('All seeders completed successfully!');
    }
}

