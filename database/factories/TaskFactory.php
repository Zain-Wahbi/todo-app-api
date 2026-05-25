<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'title'       => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status'      => fake()->randomElement(TaskStatus::cases()),
            'priority'    => fake()->randomElement(TaskPriority::cases()),
            'due_date'    => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at'=> null,
        ];
    }
}