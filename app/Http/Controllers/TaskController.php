<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Notifications\TaskInProgressReminder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('user', 'subtasks')
            ->where('user_id', auth()->id())
            ->parentOnly()
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Tasks/Index', ['tasks' => TaskResource::collection($tasks)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['description' => 'required', 'parent_task_id' => 'nullable|exists:tasks,id']);
        $parentTaskId = $validated['parent_task_id'];

        if($parentTaskId !== null)
        {
            if(Task::find($parentTaskId)->isSubtask())
            {
                return response()->json(['message' => 'Subtask cannot be added for subtasks'], 422);
            }
        }

        $request->user()->tasks()->create([
            'description' => $request->description,
            'parent_task_id' => $request->parent_task_id,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate(['description' => 'sometimes|required', 'status' => [new Enum(TaskStatus::class)]]);

        $task->update($request->all());

        $this->dispatchReminderIfInProgress($task);

        return redirect()->back();
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->back();
    }

    private function dispatchReminderIfInProgress(Task $task): void
    {
        if ($task->wasChanged('status') && $task->isInProgress()) {
            // let's only send the reminder if the task wasn't already in progress at some point in the past
            if($task->progress_started_at === null)
            {
                $task->progress_started_at = now();
                $task->save();
                $task->user->notify(new TaskInProgressReminder($task));
            }
        }
    }
}
