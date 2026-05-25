<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // Index Tests

    public function test_user_can_get_all_tasks(): void
    {
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'tasks',
                         'meta' => ['total', 'current_page', 'last_page'],
                     ],
                 ]);
    }

    public function test_user_cannot_see_other_users_tasks(): void
    {
        $otherUser = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/tasks');

        $response->assertStatus(200)
                 ->assertJsonPath('data.meta.total', 0);
    }

    public function test_user_can_filter_tasks_by_status(): void
    {
        Task::factory()->create([
            'user_id' => $this->user->id,
            'status'  => TaskStatus::Pending,
        ]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'status'  => TaskStatus::Done,
        ]);

        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/tasks?status=pending');

        $response->assertStatus(200)
                 ->assertJsonPath('data.meta.total', 1);
    }

    // Store Tests

    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/tasks', [
                             'title'    => 'New Task',
                             'priority' => 'high',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.task.title', 'New Task');

        $this->assertDatabaseHas('tasks', [
            'title'   => 'New Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_task_fails_without_title(): void
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/tasks', [
                             'priority' => 'high',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    // Show Tests

    public function test_user_can_show_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.task.id', $task->id);
    }

    public function test_user_cannot_show_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task      = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
                         ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    // Update Tests

    public function test_user_can_update_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->putJson("/api/v1/tasks/{$task->id}", [
                             'title' => 'Updated Task',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.task.title', 'Updated Task');
    }

    public function test_user_cannot_update_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task      = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
                         ->putJson("/api/v1/tasks/{$task->id}", [
                             'title' => 'Updated Task',
                         ]);

        $response->assertStatus(403);
    }

    // Complete Tests

    public function test_user_can_complete_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status'  => TaskStatus::Pending,
        ]);

        $response = $this->actingAs($this->user)
                         ->patchJson("/api/v1/tasks/{$task->id}/complete");

        $response->assertStatus(200)
                 ->assertJsonPath('data.task.status', 'done');

        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'status' => 'done',
        ]);
    }

    // Destroy Tests

    public function test_user_can_delete_own_task(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_other_users_task(): void
    {
        $otherUser = User::factory()->create();
        $task      = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/v1/tasks');

        $response->assertStatus(401);
    }
}