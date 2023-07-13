<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function subtask_cannot_be_added_for_subtasks()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $subtask = Task::factory()->create(['user_id' => $user->id, 'parent_task_id' => $task->id]);

        $response = $this->actingAs($user)
            ->post('/tasks', [
                'description' => 'Invalid Subtask',
                'parent_task_id' => $subtask->id,
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function only_task_owner_can_update_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->patch('/tasks/'.$task->id, [
                'description' => 'Updated Task',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('tasks', ['description' => 'Updated Task']);
    }

    /** @test */
    public function only_task_owner_can_delete_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->delete('/tasks/'.$task->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
