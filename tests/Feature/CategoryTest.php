<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // Index Tests

    public function test_user_can_get_all_categories(): void
    {
        Category::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/categories');

        $response->assertStatus(200)
                 ->assertJsonPath('data.total', 3);
    }

    // Store Tests

    public function test_user_can_create_category(): void
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/categories', [
                             'name'  => 'Work',
                             'color' => '#6366f1',
                             'icon'  => 'briefcase',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.category.name', 'Work');

        $this->assertDatabaseHas('categories', [
            'name'    => 'Work',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_category_fails_with_invalid_color(): void
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/categories', [
                             'name'  => 'Work',
                             'color' => 'not-a-color',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false);
    }

    // Update Tests

    public function test_user_can_update_own_category(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->putJson("/api/v1/categories/{$category->id}", [
                             'name' => 'Personal',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.category.name', 'Personal');
    }

    public function test_user_cannot_update_other_users_category(): void
    {
        $otherUser = User::factory()->create();
        $category  = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
                         ->putJson("/api/v1/categories/{$category->id}", [
                             'name' => 'Personal',
                         ]);

        $response->assertStatus(403);
    }

    // Destroy Tests

    public function test_user_can_delete_empty_category(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_user_cannot_delete_category_with_tasks(): void
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        Task::factory()->create([
            'user_id'     => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(422);
    }
}