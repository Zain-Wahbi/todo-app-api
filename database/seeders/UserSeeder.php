<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'      => 'Admin',
            'email'     => 'admin@example.com',
            'password'  => 'password123',
            'role'      => UserRole::Admin,
            'is_active' => true,
        ]);

        // Regular Users
        User::create([
            'name'      => 'John Doe',
            'email'     => 'john@example.com',
            'password'  => 'password123',
            'role'      => UserRole::User,
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Jane Doe',
            'email'     => 'jane@example.com',
            'password'  => 'password123',
            'role'      => UserRole::User,
            'is_active' => false,
        ]);
    }
}