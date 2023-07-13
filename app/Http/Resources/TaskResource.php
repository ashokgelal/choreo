<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'status' => $this->status,
            'progress_started_at' => $this->progress_started_at,
            'created_at' => $this->created_at,
            'parent_task_id' => $this->parent_task_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'subtasks' => TaskResource::collection($this->whenLoaded('subtasks')),
        ];
    }
}
