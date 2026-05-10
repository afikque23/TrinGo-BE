<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $email = 'admin@mototracker.com';
        $password = 'admin123';

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: ' . $email);
        $this->command->info('Password: ' . $password);
    }
}
