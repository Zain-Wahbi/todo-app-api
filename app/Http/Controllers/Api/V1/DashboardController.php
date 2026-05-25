<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $userId = Auth::id();

        $data = Cache::remember("dashboard_{$userId}", 300, function () use ($userId) {

            // Tasks Stats

            $totalTasks     = Task::forUser($userId)->count();
            $completedTasks = Task::forUser($userId)->completed()->count();
            $pendingTasks   = Task::forUser($userId)->pending()->count();
            $overdueTasks   = Task::forUser($userId)->overdue()->count();

            $inProgressTasks = Task::forUser($userId)
                ->where('status', TaskStatus::InProgress)
                ->count();

            // Priority Stats

            $highPriority = Task::forUser($userId)
                ->where('priority', TaskPriority::High)
                ->where('status', '!=', TaskStatus::Done)
                ->count();

            $mediumPriority = Task::forUser($userId)
                ->where('priority', TaskPriority::Medium)
                ->where('status', '!=', TaskStatus::Done)
                ->count();

            $lowPriority = Task::forUser($userId)
                ->where('priority', TaskPriority::Low)
                ->where('status', '!=', TaskStatus::Done)
                ->count();

            // Completion Rate

            $completionRate = $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100, 1)
                : 0;

            // Recent Tasks

            $recentTasks = Task::forUser($userId)
                ->with('category')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($task) => [
                    'id'       => $task->id,
                    'title'    => $task->title,
                    'status'   => $task->status->value,
                    'priority' => $task->priority->value,
                    'due_date' => $task->due_date?->toDateString(),
                    'category' => $task->category?->name,
                ]);

            // Upcoming Tasks

            $upcomingTasks = Task::forUser($userId)
                ->with('category')
                ->whereNotNull('due_date')
                ->where('due_date', '>=', now())
                ->where('status', '!=', TaskStatus::Done)
                ->orderBy('due_date')
                ->take(5)
                ->get()
                ->map(fn($task) => [
                    'id'       => $task->id,
                    'title'    => $task->title,
                    'status'   => $task->status->value,
                    'priority' => $task->priority->value,
                    'due_date' => $task->due_date?->toDateString(),
                    'category' => $task->category?->name,
                ]);

            // Categories Stats

            $categoriesStats = Auth::user()
                ->categories()
                ->withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn($q) =>
                        $q->where('status', TaskStatus::Done),
                ])
                ->get()
                ->map(fn($cat) => [
                    'id'              => $cat->id,
                    'name'            => $cat->name,
                    'color'           => $cat->color,
                    'icon'            => $cat->icon,
                    'total_tasks'     => $cat->tasks_count,
                    'completed_tasks' => $cat->completed_tasks_count,
                ]);

            return [
                'stats' => [
                    'total'           => $totalTasks,
                    'completed'       => $completedTasks,
                    'pending'         => $pendingTasks,
                    'in_progress'     => $inProgressTasks,
                    'overdue'         => $overdueTasks,
                    'completion_rate' => $completionRate . '%',
                ],
                'priority_breakdown' => [
                    'high'   => $highPriority,
                    'medium' => $mediumPriority,
                    'low'    => $lowPriority,
                ],
                'recent_tasks'     => $recentTasks,
                'upcoming_tasks'   => $upcomingTasks,
                'categories_stats' => $categoriesStats,
            ];
        });

        return $this->successResponse($data);
    }
}