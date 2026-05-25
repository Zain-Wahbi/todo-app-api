<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $data = Cache::remember('admin_dashboard', 300, function () {

            $totalUsers    = User::count();
            $activeUsers   = User::active()->count();
            $adminUsers    = User::admins()->count();
            $inactiveUsers = User::where('is_active', false)->count();

            $totalTasks     = Task::count();
            $completedTasks = Task::completed()->count();
            $overdueTasks   = Task::overdue()->count();
            $pendingTasks   = Task::pending()->count();

            $completionRate = $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100, 1)
                : 0;

            $totalCategories = Category::count();

            $mostActiveUsers = User::withCount('tasks')
                ->orderByDesc('tasks_count')
                ->take(5)
                ->get()
                ->map(fn($user) => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'tasks_count' => $user->tasks_count,
                ]);

            $recentUsers = User::latest()
                ->take(5)
                ->get()
                ->map(fn($user) => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role->value,
                    'is_active'  => $user->is_active,
                    'created_at' => $user->created_at->toDateTimeString(),
                ]);

            return [
                'users_stats' => [
                    'total'    => $totalUsers,
                    'active'   => $activeUsers,
                    'inactive' => $inactiveUsers,
                    'admins'   => $adminUsers,
                ],
                'tasks_stats' => [
                    'total'           => $totalTasks,
                    'completed'       => $completedTasks,
                    'pending'         => $pendingTasks,
                    'overdue'         => $overdueTasks,
                    'completion_rate' => $completionRate . '%',
                ],
                'total_categories'  => $totalCategories,
                'most_active_users' => $mostActiveUsers,
                'recent_users'      => $recentUsers,
            ];
        });

        return $this->successResponse($data);
    }
}