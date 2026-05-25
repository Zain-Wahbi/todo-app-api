<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class TaskController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $tasks = Task::forUser(Auth::id())
            ->with('category')
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status))
            ->when($request->priority, fn($q) =>
                $q->where('priority', $request->priority))
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id))
            ->when($request->search, fn($q) =>
                $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->overdue, fn($q) =>
                $q->overdue())
            ->latest()
            ->paginate(10);

        return $this->successResponse([
            'tasks' => TaskResource::collection($tasks),
            'meta'  => [
                'total'        => $tasks->total(),
                'current_page' => $tasks->currentPage(),
                'last_page'    => $tasks->lastPage(),
            ],
        ]);
    }
    private function clearDashboardCache(): void
    {
        Cache::forget('dashboard_' . Auth::id());
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        $this->clearDashboardCache();

        return $this->createdResponse(
            ['task' => new TaskResource($task->load('category'))],
            'Task created successfully'
        );
    }

    public function show(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse([
            'task' => new TaskResource($task->load('category')),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }

        $data = $request->validated();

        if (isset($data['status']) && $data['status'] === TaskStatus::Done->value) {
            $data['completed_at'] = now();
        }

        if (isset($data['status']) && $data['status'] !== TaskStatus::Done->value) {
            $data['completed_at'] = null;
        }

        $task->update($data);

        $this->clearDashboardCache();

        return $this->successResponse(
            ['task' => new TaskResource($task->load('category'))],
            'Task updated successfully'
        );
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }
        $task->delete();
        $this->clearDashboardCache();
        return $this->deletedResponse('Task deleted successfully');
    }

    public function complete(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return $this->forbiddenResponse();
        }

        $task->update([
            'status'       => TaskStatus::Done,
            'completed_at' => now(),
        ]);

        $this->clearDashboardCache();

        return $this->successResponse(
            ['task' => new TaskResource($task->load('category'))],
            'Task marked as completed'
        );
    }
}