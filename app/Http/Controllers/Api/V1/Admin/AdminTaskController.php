<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    // Index Task for all user

    public function index(Request $request): JsonResponse
    {
        $tasks = Task::with(['user', 'category'])
            ->when($request->search, fn($q) =>
                $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status))
            ->when($request->priority, fn($q) =>
                $q->where('priority', $request->priority))
            ->when($request->user_id, fn($q) =>
                $q->where('user_id', $request->user_id))
            ->when($request->overdue, fn($q) =>
                $q->overdue())
            ->latest()
            ->paginate(15);

        return response()->json([
            'tasks' => $tasks->map(fn($task) => [
                'id'       => $task->id,
                'title'    => $task->title,
                'status'   => $task->status->value,
                'priority' => $task->priority->value,
                'due_date' => $task->due_date?->toDateString(),
                'is_overdue' => $task->isOverdue(),
                'user'     => [
                    'id'    => $task->user->id,
                    'name'  => $task->user->name,
                    'email' => $task->user->email,
                ],
                'category' => $task->category?->name,
            ]),
            'meta' => [
                'total'        => $tasks->total(),
                'current_page' => $tasks->currentPage(),
                'last_page'    => $tasks->lastPage(),
            ],
        ]);
    }

    // Destroy Task

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}