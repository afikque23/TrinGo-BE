<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // Create a default user for quick login (mobile/testing)
        $email = 'user@mototracker.com';
        $password = 'user123';

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'User Demo',
                'password' => Hash::make($password),
                'role' => 'user',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->command->info('Default user created successfully!');
        $this->command->info('Email: ' . $email);
        $this->command->info('Password: ' . $password);

        $this->command->info('All seeders completed successfully!');
    }
}

