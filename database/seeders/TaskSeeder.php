<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $john = User::where('email', 'john@example.com')->first();

        $work     = Category::where('name', 'Work')->first();
        $personal = Category::where('name', 'Personal')->first();
        $study    = Category::where('name', 'Study')->first();

        $tasks = [
            // Work Tasks
            [
                'title'       => 'Finish project report',
                'description' => 'Complete the Q4 report for the team',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::High,
                'due_date'    => now()->addDays(2),
                'category_id' => $work->id,
            ],
            [
                'title'       => 'Team meeting',
                'description' => 'Weekly sync with the team',
                'status'      => TaskStatus::Pending,
                'priority'    => TaskPriority::Medium,
                'due_date'    => now()->addDays(1),
                'category_id' => $work->id,
            ],
            [
                'title'       => 'Review pull requests',
                'description' => 'Review and merge open PRs',
                'status'      => TaskStatus::Done,
                'priority'    => TaskPriority::High,
                'due_date'    => now()->subDays(1),
                'completed_at'=> now()->subDays(1),
                'category_id' => $work->id,
            ],

            // Personal Tasks
            [
                'title'       => 'Gym workout',
                'description' => 'Chest and shoulders day',
                'status'      => TaskStatus::Pending,
                'priority'    => TaskPriority::Low,
                'due_date'    => now()->addDays(1),
                'category_id' => $personal->id,
            ],
            [
                'title'       => 'Buy groceries',
                'description' => 'Milk, eggs, bread, vegetables',
                'status'      => TaskStatus::Pending,
                'priority'    => TaskPriority::Medium,
                'due_date'    => now()->subDays(2),
                'category_id' => $personal->id,
            ],

            // Study Tasks
            [
                'title'       => 'Learn Laravel API',
                'description' => 'Complete the API project',
                'status'      => TaskStatus::InProgress,
                'priority'    => TaskPriority::High,
                'due_date'    => now()->addDays(7),
                'category_id' => $study->id,
            ],
            [
                'title'       => 'Read Clean Code book',
                'description' => 'Read chapters 1-5',
                'status'      => TaskStatus::Pending,
                'priority'    => TaskPriority::Low,
                'due_date'    => now()->addDays(14),
                'category_id' => $study->id,
            ],

            // Task بدون Category
            [
                'title'       => 'Check emails',
                'description' => null,
                'status'      => TaskStatus::Pending,
                'priority'    => TaskPriority::Low,
                'due_date'    => null,
                'category_id' => null,
            ],
        ];

        foreach ($tasks as $task) {
            Task::create([
                ...$task,
                'user_id' => $john->id,
            ]);
        }
    }
}