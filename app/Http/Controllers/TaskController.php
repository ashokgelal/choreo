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
}
