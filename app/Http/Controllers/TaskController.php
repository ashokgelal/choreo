<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
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
            ->get();

        return Inertia::render('Tasks/Index', ['tasks' => TaskResource::collection($tasks)]);
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

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->back();
    }
}
