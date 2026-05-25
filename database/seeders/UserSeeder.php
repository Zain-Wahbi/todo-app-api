<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'      => 'Admin',
                'password'  => 'password123',
                'role'      => UserRole::Admin,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name'      => 'John Doe',
                'password'  => 'password123',
                'role'      => UserRole::User,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name'      => 'Jane Doe',
                'password'  => 'password123',
                'role'      => UserRole::User,
                'is_active' => false,
            ]
        );
    }
}