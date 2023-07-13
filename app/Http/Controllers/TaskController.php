<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('user', 'subtasks')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'description' => $task->description,
                    'status' => $task->status,
                    'progress_started_at' => $task->progress_started_at,
                    'created_at' => $task->created_at,
                    'user' => [
                        'id' => $task->user->id,
                        'name' => $task->user->name,
                    ],
                ];
            });
        return Inertia::render('Tasks/Index', ['tasks' => $tasks]);
    }

    public function store(Request $request)
    {
        $request->validate(['description' => 'required']);

        $request->user()->tasks()->create([
            'description' => $request->description,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate(['description' => 'required']);

        $task->update($request->all());

        return redirect()->back();
    }
}
