<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $john = User::where('email', 'john@example.com')->first();

        $categories = [
            [
                'name'  => 'Work',
                'color' => '#6366f1',
                'icon'  => 'briefcase',
            ],
            [
                'name'  => 'Personal',
                'color' => '#22c55e',
                'icon'  => 'user',
            ],
            [
                'name'  => 'Study',
                'color' => '#f59e0b',
                'icon'  => 'book',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                ...$category,
                'user_id' => $john->id,
            ]);
        }
    }
}