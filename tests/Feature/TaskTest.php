<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Notifications\TaskInProgressReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_only_returns_tasks_owned_by_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userTask1 = Task::factory()->create(['user_id' => $user->id]);
        $userTask2 = Task::factory()->create(['user_id' => $user->id]);
        $otherUserTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user)
            ->get('/tasks')
            ->assertInertia(fn(Assert $page) => $page
                ->component('Tasks/Index')
                ->has('tasks.data', 2)
                ->where('tasks.data.0.id', $userTask1->id)
                ->where('tasks.data.1.id', $userTask2->id)
                ->missing('tasks.data.*.id', $otherUserTask->id)
            );
    }

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
            ->patch('/tasks/' . $task->id, [
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
            ->delete('/tasks/' . $task->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function updating_task_status_to_in_progress_dispatches_task_in_progress_reminder_notification()
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value, 'progress_started_at' => null]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::IN_PROGRESS->value,
            ]);

        Notification::assertSentTo($user, TaskInProgressReminder::class, function ($notification, $channels) use ($task) {
            return $notification->task->id === $task->id;
        });
    }

    /** @test */
    public function updating_task_status_to_in_progress_multiple_times_does_not_dispatch_task_in_progress_reminder_notification()
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value, 'progress_started_at' => null]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::IN_PROGRESS->value,
            ]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::TODO->value,
            ]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::IN_PROGRESS->value,
            ]);

        Notification::assertSentTimes(TaskInProgressReminder::class, 1);
    }

    /** @test */
    public function updating_task_only_dispatches_notification_for_in_progress_task()
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value, 'progress_started_at' => null]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::DONE->value,
            ]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function updating_task_status_validates_enum_values()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'status' => 'invalid_status'
            ])->assertSessionHasErrors('status');

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'status' => TaskStatus::DONE->value
            ])->assertSessionDoesntHaveErrors('status');
    }

    /** @test */
    public function reminder_is_not_sent_if_task_no_longer_in_progress()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value, 'progress_started_at' => null]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::IN_PROGRESS->value,
            ]);

        $task->status = TaskStatus::DONE->value;
        $task->save();

        Notification::fake();
        Notification::send($user, new TaskInProgressReminder($task));
        Notification::assertNothingSent();
    }

    /** @test */
    public function in_progress_task_reminder_gets_queued()
    {
        Queue::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => TaskStatus::TODO->value, 'progress_started_at' => null]);

        $this->actingAs($user)
            ->put('/tasks/' . $task->id, [
                'description' => $task->description,
                'status' => TaskStatus::IN_PROGRESS->value,
            ]);

        Queue::assertPushed(SendQueuedNotifications::class, function ($job) use ($task) {
            return $job->notification->task->id === $task->id;
        });
    }

}
