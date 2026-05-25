<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Enum;

class AdminUserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $users = User::withCount('tasks')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) =>
                $q->where('role', $request->role))
            ->when(isset($request->is_active), fn($q) =>
                $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(15);

        return $this->successResponse([
            'users' => $users->map(fn($user) => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->role->value,
                'role_label'  => $user->role->label(),
                'is_active'   => $user->is_active,
                'tasks_count' => $user->tasks_count,
                'created_at'  => $user->created_at->toDateTimeString(),
            ]),
            'meta' => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    public function show(User $user)
    {
        $user->loadCount(['tasks', 'categories']);

        return $this->successResponse([
            'user' => [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'role'             => $user->role->value,
                'role_label'       => $user->role->label(),
                'is_active'        => $user->is_active,
                'tasks_count'      => $user->tasks_count,
                'categories_count' => $user->categories_count,
                'created_at'       => $user->created_at->toDateTimeString(),
            ],
        ]);
    }

    public function toggleActive(User $user)
    {
        if ($user->id === Auth::id()) {
            return $this->errorResponse(
                'You cannot deactivate your own account', 422
            );
        }

        $user->update(['is_active' => !$user->is_active]);

        // 🧹 مسح كاش الأدمن
        Cache::forget('admin_dashboard');

        Log::channel('admin')->info('User active status toggled', [
            'admin_id'   => Auth::id(),
            'user_id'    => $user->id,
            'email'      => $user->email,
            'is_active'  => $user->is_active,
        ]);

        return $this->successResponse([
            'is_active' => $user->is_active,
        ], $user->is_active ? 'User activated' : 'User deactivated');
    }

    public function changeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        if ($user->id === Auth::id()) {
            return $this->errorResponse(
                'You cannot change your own role', 422
            );
        }

        $oldRole = $user->role->value;
        $user->update(['role' => $request->role]);

        // 🧹 مسح كاش الأدمن
        Cache::forget('admin_dashboard');

        Log::channel('admin')->info('User role changed', [
            'admin_id' => Auth::id(),
            'user_id'  => $user->id,
            'email'    => $user->email,
            'old_role' => $oldRole,
            'new_role' => $request->role,
        ]);

        return $this->successResponse([
            'role' => $user->role->value,
        ], 'User role updated successfully');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return $this->errorResponse(
                'You cannot delete your own account', 422
            );
        }

        Log::channel('admin')->warning('User deleted', [
            'admin_id'        => Auth::id(),
            'deleted_user_id' => $user->id,
            'email'           => $user->email,
        ]);

        $user->delete();

        Cache::forget('admin_dashboard');

        return $this->deletedResponse('User deleted successfully');
    }
}
