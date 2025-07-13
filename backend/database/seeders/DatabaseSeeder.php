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
        // User::factory(10)->create();

        // Create a test user
        User::create([
            'first_name' => 'Base',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true,
            'is_staff' => false,
            'email_verified_at' => now(),
        ]);

        // Create an admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'is_active' => true,
            'is_staff' => true,
            'email_verified_at' => now(),
        ]);
    }
}
