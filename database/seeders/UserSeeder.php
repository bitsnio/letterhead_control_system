<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@letterhead.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Manager User
        \App\Models\User::create([
            'name' => 'Manager User',
            'email' => 'manager@letterhead.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Staff User
        \App\Models\User::create([
            'name' => 'Staff User',
            'email' => 'staff@letterhead.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Printer User
        \App\Models\User::create([
            'name' => 'Printer User',
            'email' => 'printer@letterhead.com',
            'password' => bcrypt('password'),
            'role' => 'printer',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
